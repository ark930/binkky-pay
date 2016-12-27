<?php

namespace App\Libraries\Channel\Wechat;

use App\Exceptions\APIException;
use App\Libraries\Channel\Helper;
use App\Models\Charge;

class WechatScan extends WechatBase
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
            'time_start'       => $this->formatTime($charge['created_at']),
            'time_expire'      => $this->formatTime($charge['expired_at']),
            'auth_code'        => $charge['auth_code'],
        ];

        $req['sign'] = $this->signArray($req, $this->key);
        $reqXml = Helper::arrayToXml($req);

        $res = $this->request($this->getUrl('scan.pay'), $reqXml);

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

        $charge['transaction_no'] = $res['transaction_id'];
        $charge['amount_settled'] = $res['cash_fee'];
        $charge['status'] = Charge::STATUS_SUCCEEDED;
        $charge['paid_at'] = date('Y-m-d H:i:s', strtotime($res['time_end']));
        $charge->save();

        return parent::charge($charge);
    }

}