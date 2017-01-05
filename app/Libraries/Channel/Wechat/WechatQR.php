<?php

namespace App\Libraries\Channel\Wechat;

use App\Exceptions\APIException;
use App\Libraries\Channel\Helper;
use App\Models\Charge;

class WechatQR extends WechatBase
{
    public function charge(Charge $charge)
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
            'notify_url'       => $charge['notify_url'],
            'trade_type'       => self::TRADE_TYPES['qr'],
        ];

        $req['product_id'] = $charge['product_id'];

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

        if (!isset($res['code_url']))
        {
            throw new APIException('渠道返回解析失败');
        }

        $this->credential = $res['code_url'];
        return parent::charge($charge);
    }
}