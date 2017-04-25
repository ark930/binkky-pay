<?php

namespace App\Libraries\Channel\Wechat;


use App\Exceptions\APIException;
use App\Libraries\Channel\Helper;
use App\Models\Charge;

class WechatApp extends WechatBase
{
    public function charge(Charge $charge, array $params = [])
    {
        $req = [
            'appid'            => $this->appId,
            'mch_id'           => $this->mchId,
            'nonce_str'        => $this->generateNonceString($charge['trade_no']),
            'body'             => $charge['title'],
            'out_trade_no'     => $charge['trade_no'],
            'total_fee'        => $charge['amount'],
            'spbill_create_ip' => $charge['client_ip'],
            'time_start'       => $this->formatTime($charge['created_at']),
            'time_expire'      => $this->formatTime($charge['expired_at']),
            'notify_url'       => $this->getNotifyUrl($charge['id']),
            'trade_type'       => self::TRADE_TYPES['app'],
        ];

        $req['sign'] = $this->signArray($req, $this->key);
        $reqXml = Helper::arrayToXml($req);

        $res = $this->request($this->getUrl('pay'), $reqXml);

        if ($res['return_code'] != 'SUCCESS')
        {
            throw new APIException('渠道请求失败');
        }

        $this->verifyResponse($res, $this->key);
        if ($res['result_code'] != 'SUCCESS')
        {
            if ($res['err_code'] == 'OUT_TRADE_NO_USED')
            {
                throw new APIException('订单号已使用');
            }
            throw new APIException('渠道请求失败:' . $res['err_code'] . '/' . $res['err_code_des']);
        }

        if (!isset($res['prepay_id']))
        {
            throw new APIException('渠道返回解析失败');
        }

        $credential = [
            'appid'     => $this->appId,
            'partnerid' => $this->mchId,
            'prepayid'  => $res['prepay_id'],
            'package'   => 'Sign=WXPay',
            'noncestr'  => $this->generateNonceString($charge['trade_no']),
            'timestamp' => time() . '',
        ];

        $credential['sign'] = $this->signArray($credential, $this->key);
        $this->credential = $credential;

        return parent::charge($charge);
    }
}