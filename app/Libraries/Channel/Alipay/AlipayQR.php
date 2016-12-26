<?php

namespace App\Libraries\Channel\Alipay;

use App\Exceptions\BadRequestException;
use App\Models\Charge;

class AlipayQR extends AlipayBase
{
    public function charge(Charge $charge)
    {
        $bizContent = [
            'subject'           => $charge['subject'],
            'body'              => $charge['body'],
            'out_trade_no'      => $charge['order_no'],
            'timeout_express'   => $this->makeExpiredTime($charge['expired_at']),
            'total_amount'      => $this->formatAmount($charge['amount']),
        ];

        $commonParams = $this->makeCommonParameters(self::METHODS['qrcode.pay'], $charge['created_at']);
        $commonParams['notify_url'] = $this->makeNotifyUrl($charge['id']);
        $commonParams['biz_content'] = json_encode($bizContent);

        $requestUrl = $this->makeRequestUrl($commonParams);

        $this->initHttpClient('');
        $res = $this->requestPlainText('GET', $requestUrl);
        $res = \GuzzleHttp\json_decode($res, true);

        $res = $res[self::RESPONSE_KEY['qrcode.pay']];
        if($res['code'] === '10000' && $res['out_trade_no'] === $charge['order_no']) {
            return [
                'credential' => $res['qr_code'],
            ];
        }

        throw new BadRequestException('请求失败:' . $res['code']);
    }
}