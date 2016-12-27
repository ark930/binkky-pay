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
            'nonce_str'        => $this->generateNonceString($charge['order_no']),
            'body'             => $charge['body'],
            'out_trade_no'     => $charge['order_no'],
            'total_fee'        => $charge['amount'],
            'spbill_create_ip' => $charge['client_ip'],
            'time_start'       => date('YmdHis', strtotime($charge['created_at'])),
            'notify_url'       => $this->makeNotifyUrl($charge['id']),
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

        return [
            'credential' => $res['code_url'],
        ];
    }
}