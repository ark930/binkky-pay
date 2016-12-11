<?php

namespace App\Libraries;

class AlipayWap implements IPayment
{
    const METHOD_WAP = 'alipay.trade.wap.pay';
    const FORMAT_JSON = 'JSON';
    const VERSION_1_0 = '1.0';
    const SIGN_TYPE_RSA = 'RSA';
    const CHARSET_UTF8 = 'UTF-8';
    const GATEWAY_URL = "https://openapi.alipay.com/gateway.do";

    protected $appId = null;
    protected $privateKey = null;

    public function __construct($channelParams)
    {
        $this->appId = $channelParams->appid;
        $this->privateKey = $channelParams->private_key;
    }

    public function create(array $chargeParams)
    {
        $bizContent = [
            'body' => $chargeParams['body'],
            'subject' => $chargeParams['subject'],
            'out_trade_no' => $chargeParams['out_trade_no'],
            'timeout_express' => '90m',
            'total_amount' => $chargeParams['total_amount'],
            'product_code' => 'QUICK_WAP_PAY',
        ];

        $totalParams = [
            'app_id' => $this->appId,
            'method' => self::METHOD_WAP,
            'format' => self::FORMAT_JSON,
//            'return_url' => '',
            'charset' => self::CHARSET_UTF8,
            'sign_type' => self::SIGN_TYPE_RSA,
            'timestamp' => $chargeParams['timestamp'],
            'version' => self::VERSION_1_0,
//            'notify_url' => '',
            'biz_content' => json_encode($bizContent),
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
            if ( "@" != substr($v, 0, 1)) {

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