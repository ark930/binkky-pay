<?php

namespace App\Libraries\Channel\Alipay;

abstract class AlipayBase
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
}