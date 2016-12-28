<?php

namespace App\Libraries\Channel\UnionPay;

use App\Libraries\Channel\Helper;
use App\Libraries\Channel\IPayment;
use App\Models\Charge;
use App\Models\Refund;

class UnionPayBase extends IPayment
{
    const BASE_URL = 'https://gateway.95516.com';
    const BASE_URL_TESTING = 'https://101.231.204.80:5000';

    const VERSION_5_0_0 = '5.0.0';
    const ENCODING = 'UTF-8';
    const SIGN_TYPE_RSA = 'RSA';
    const CURRENCY_CODES = [
        'cny' => '156',
    ];
    const TRANSACTION_TYPES = [
        'sale' => '01',
    ];
    const TRANSACTION_SUB_TYPES = [
        'sale' => '01',
    ];
    const BIZ_TYPES = [
        'B2C' => '000201',
    ];
    const CHANNEL_TYPES = [
        'mobile' => '08',
    ];
    const ACCESS_TYPE = [
        'merchant' => '0'
    ];
    const ACTIONS = [
        'wap.pay'       => '/gateway/api/frontTransReq.do',
        'query'         => '/gateway/api/queryTrans.do',
    ];

    // 银联参数变量
    protected $merId;
    protected $certId;
    protected $cert;
    protected $certPassword;

    public function __construct($channelParams)
    {
        $this->merId = $channelParams['mer_id'];
        $this->certId = $channelParams['cert_id'];
        $this->cert = $channelParams['cert'];
        $this->certPassword = $channelParams['cert_password'];

        $this->baseUrl = self::BASE_URL;
        parent::__construct();
    }

    public function setTesting()
    {
        $this->baseUrl = self::BASE_URL_TESTING;

        // 银联测试参数
        $this->merId = '777290058110048';
        $this->certId = '68759663125';
        $this->cert = '-----BEGIN CERTIFICATE-----
MIIESjCCAzKgAwIBAgIFEAJlMhUwDQYJKoZIhvcNAQEFBQAwWDELMAkGA1UEBhMC
Q04xMDAuBgNVBAoTJ0NoaW5hIEZpbmFuY2lhbCBDZXJ0aWZpY2F0aW9uIEF1dGhv
cml0eTEXMBUGA1UEAxMOQ0ZDQSBURVNUIE9DQTEwHhcNMTUxMjE2MDgyNTA3WhcN
MTgxMjE2MDgyNTA3WjCBiDELMAkGA1UEBhMCY24xFzAVBgNVBAoTDkNGQ0EgVEVT
VCBPQ0ExMRIwEAYDVQQLEwlDRkNBIFRFU1QxFDASBgNVBAsTC0VudGVycHJpc2Vz
MTYwNAYDVQQDFC0wNDFAWjIwMTQtMTEtMTFANzAwMDAwMDAwMDAwMDAxOlNJR05A
MDAwMDAwMDYwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDZgpE+JFjx
fSAQz7haGrCD62/babFa/8cDnlGKlfpvl0fClz7rNyyJ8XgRDwzXx+7IAl5xANuQ
SrqFEbyhtrcmMZE6xULpW7peDmTtggC6eLVZ04eFh3FeVw26zlk3BhS7ZEcifJi7
gPwgIW36YlsJNkm8kq45g5MnysXqeodQ9BsWmIJw7rCy7b4IWcsPrsTZ3dEQVbP6
/sAxXjeRx6xrSbmUFDlLYqP3+RTv2sx+poBto/i9AqV/yi2B54Ev8hr5p4C9PVoL
1SoE4DijWgZigJtBf5YfPuJ9wRk5JbYKF4SeQ+hV0l0bPLd/nsxP1kSmG7eRQ6DJ
LzhSnuuj+8vjAgMBAAGjgekwgeYwHwYDVR0jBBgwFoAUz3CdYeudfC6498sCQPcJ
nf4zdIAwSAYDVR0gBEEwPzA9BghggRyG7yoBATAxMC8GCCsGAQUFBwIBFiNodHRw
Oi8vd3d3LmNmY2EuY29tLmNuL3VzL3VzLTE0Lmh0bTA4BgNVHR8EMTAvMC2gK6Ap
hidodHRwOi8vdWNybC5jZmNhLmNvbS5jbi9SU0EvY3JsNDUyNi5jcmwwCwYDVR0P
BAQDAgPoMB0GA1UdDgQWBBQmnndv0H4naGdnCtQkt9LXR2s0XTATBgNVHSUEDDAK
BggrBgEFBQcDAjANBgkqhkiG9w0BAQUFAAOCAQEAr/678pvZ5/8f0P1nrSyS16Pa
Eejk32Flq5vKQ+ifgI/C4vG8wOTxKDzERTqi1OLsswy7++WegJWhQ577U7OcEpw+
teRTiYDnq0jrVxWR2yTtwKeb/H3ksJuk8S0angmHLePinfrhs8khn4Zu/7lfqJXg
ckCh1nKX0CilrR83e9BBXBnM8XfRfWFvPoJk2EALF4eP53Vca7G4QX/82WU0iCTa
zD441mqUl+jdP1yKSFp84vmYqUKq6/73Tik2AExRGNNp90AYErtNS3eza6pjdwbY
N/qcw7dAhbijB1978k0FJ8LwiEQ8rIXhxS+AxYVSAWxSAFkDA2V00NnQSHyBSA==
-----END CERTIFICATE-----';
        $this->certPassword = '000000';
    }

    public function query(Charge $charge)
    {

    }

    public function notify(Charge $charge, array $notify)
    {

    }

    public function refund(Charge $charge, Refund $refund)
    {

    }

    public function refundQuery(Charge $charge, Refund $refund)
    {

    }
    protected function signArray($req, $privateKey)
    {
        $signArray = Helper::removeKeys($req, ['signature']);
        $signArray = Helper::removeEmpty($signArray);
        ksort($signArray);
        $signString = Helper::joinToString($signArray);

        openssl_sign($signString, $sign, $privateKey);
        $signArray['signature'] = base64_encode($sign);

        return $signArray;
    }

    protected function formatTime($time)
    {
        if(empty($time)) {
            return null;
        }

        return date('YmdHis', strtotime($time));
    }
}