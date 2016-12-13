<?php

namespace App\Libraries\Channel\Alipay;

use App\Libraries\Channel\IPayment;

class AlipayWap extends AlipayBase implements IPayment
{
    public function create(array $chargeParams)
    {
        $bizContent = [
            'body'              => $chargeParams['body'],
            'subject'           => $chargeParams['subject'],
            'out_trade_no'      => $chargeParams['out_trade_no'],
            'timeout_express'   => $chargeParams['timeout_express'],
            'total_amount'      => $chargeParams['total_amount'],
            'product_code'      => 'QUICK_WAP_PAY',
        ];

        $totalParams = [
            'app_id'        => $this->appId,
            'method'        => self::METHOD_WAP,
            'format'        => self::FORMAT_JSON,
            'return_url'    => $chargeParams['return_url'],
            'charset'       => self::CHARSET_UTF8,
            'sign_type'     => self::SIGN_TYPE_RSA,
            'timestamp'     => $chargeParams['timestamp'],
            'version'       => self::VERSION_1_0,
            'notify_url'    => $chargeParams['notify_url'],
            'biz_content'   => json_encode($bizContent),
        ];

        $preSignStr = $this->getSignContent($totalParams);
        $sign =  $this->sign($preSignStr, $this->privateKey);
        $requestUrl = self::GATEWAY_URL."?".$preSignStr."&sign=".urlencode($sign);

        return $requestUrl;
    }

    protected function getSignContent($params) {
        ksort($params);

        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if ("@" != substr($v, 0, 1)) {

                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }

        unset ($k, $v);

        return $stringToBeSigned;
    }

    protected function sign($data, $privateKey) {
        openssl_sign($data, $sign, $privateKey);
        $sign = base64_encode($sign);

        return $sign;
    }
}