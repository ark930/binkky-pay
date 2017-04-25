<?php

namespace App\Libraries\Channel\Wechat;

use App\Exceptions\APIException;
use App\Libraries\Channel\Helper;
use App\Models\Charge;

class WechatPub extends WechatBase
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
            'time_start'       => date('YmdHis', strtotime($charge['created_at'])),
            'notify_url'       => $this->getNotifyUrl($charge['id']),
            'trade_type'       => self::TRADE_TYPES['pub'],
            'openid'           => $params['auth_code'],
        ];

        $req['sign'] = $this->signArray($req, $this->key);
        $reqXml = Helper::arrayToXml($req);

        $res = $this->request($this->getUrl('pay'), $reqXml);

        if ($res['return_code'] != 'SUCCESS')
        {
            throw new APIException('渠道请求失败:' . $res['err_code'] . '/' . $res['err_code_des']);
        }

        $this->verifyResponse($res, $this->key);
        if ($res['result_code'] != 'SUCCESS')
        {
            if ($res['err_code'] == 'OUT_TRADE_NO_USED')
            {
                throw new APIException('订单号已使用');
            }
            throw new APIException('渠道请求失败');
        }

        $this->credential = $res['prepay_id'];
        return parent::charge($charge);
    }

}