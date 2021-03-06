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
    const SIGN_TYPE_RSA = '01';
    const CURRENCY_CODES = [
        'cny' => '156',
    ];
    const TRANSACTION_TYPES = [
        'query' => '00',
        'sale' => '01',
    ];
    const TRANSACTION_SUB_TYPES = [
        'query' => '00',
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
    protected $certPrivateKey;

    public function __construct($channelParams)
    {
        $this->merId = $channelParams['mer_id'];
        $this->certId = $channelParams['cert_id'];
        $this->certPrivateKey = $channelParams['cert_private_key'];

        $this->baseUrl = self::BASE_URL;
        parent::__construct();
    }

    public function setTesting()
    {
        $this->baseUrl = self::BASE_URL_TESTING;

        // 银联测试参数
        $this->merId = '777290058110048';
        $this->certId = '68759663125';
        $this->certPrivateKey = '-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDZgpE+JFjxfSAQ
z7haGrCD62/babFa/8cDnlGKlfpvl0fClz7rNyyJ8XgRDwzXx+7IAl5xANuQSrqF
EbyhtrcmMZE6xULpW7peDmTtggC6eLVZ04eFh3FeVw26zlk3BhS7ZEcifJi7gPwg
IW36YlsJNkm8kq45g5MnysXqeodQ9BsWmIJw7rCy7b4IWcsPrsTZ3dEQVbP6/sAx
XjeRx6xrSbmUFDlLYqP3+RTv2sx+poBto/i9AqV/yi2B54Ev8hr5p4C9PVoL1SoE
4DijWgZigJtBf5YfPuJ9wRk5JbYKF4SeQ+hV0l0bPLd/nsxP1kSmG7eRQ6DJLzhS
nuuj+8vjAgMBAAECggEBAIF/7k+sPlRQ5gV8Vss2tr9kLm3ZCKIgRPIPkYbMcpV7
4Vqmx+wtJlRestidOE1EmRL17hqjoxXOmCxf+gniCjswKcJu7b6YQWZ8dXS/AQYE
lhyMB1Tl5gaAGAmPj6hl83P6aSvMOPbx/ap3nM4FPyRF8TLXtelCQBvh62IGX4g/
JML4nDXkpQ4Oq8fO1x+Oi2OiJZQLT+h3OsRzb5Wa94q9FfaGCb+87V2Wa7lNjxdZ
IfzrA97ldHFrVaEltfK8FKPiCD/Dk+Q9eSybtexMh8339VybGVFW3THB++X3lgYs
wkb+KXTkpkA/vyhFEkPLWhvAHor9nhY+73u5YDkHShkCgYEA8uNMaqyGOuOSjr+s
vLyyLjlju9RR7IErQZbhQspZTgjMud/TsFyWdjBpoDTs8+T1PGMT01j06H/f4w+W
Z/SKLQAI87PDq+KDXAeKvz/JHXQ2iSDH/mHe7Ml1iQnSqHd37PjMCk9f9n4MC/4X
Ox1JdfZR8rPd+SzqshSmI8wd4j8CgYEA5UCMPoM7ouy7LaqxHhtrAJI6ydrckKJ9
X+Ms0IDl1CX6v0P0ovRvfgJtHTj+uzVGu/EFfT+easrWw+YdU2lHlqLzsOFtveHt
jSDAGue+41ZwT+oqsPNPlIvaQjV0qHUnOsWUu3mfdc9Lt8gEuDJFOoBZ77n8oJJL
gchrxHYypV0CgYEA5okPcwCltydhZ9ROJCYGCRG3tAPmblB7uhl3XWmqMgLwLkxg
JLj8ptl0p/cUILpkehigLK32ZudYna+h1rGopOWvmYA6bN7mR2dxLe1g+m/fg3B1
4uEKMj1VLekA5Z3fWjEbmX2VW+RvksJtUlKN80UEqxRFz8fuS3CF8NxAUQkCgYBA
fXv2SeyI1JeDLTVOBuB+9KPdDNhnR46FXt7IeLouh9CV5YP4I1MJ25zeT545A6+2
RwMITNE/sXfg++bcBA3DbmunIoNAm0G8Ja5k4zRrt3E4yeLgjFGitATeAzOh//Ld
MZ+5bWlSNtJSDM5nEp0u69Rg/6z1brIW/E50odt1cQKBgG4pVWf3y3M5L1Y+wQx3
3xzHSsu//GQDRJjbiEtzNjQK822g6YRWjN20p3CDeaJpPRIUH062eSJoNNSGVGtq
hk75g2vUSxYOs9AT25a0sb7IHHi+COOHQfpB8xWjt0PJCccGiYQiKqqNU5pGkzUQ
8eoxsUyQX3A5791s2EK6csEq
-----END PRIVATE KEY-----';
    }

    public function query(Charge $charge)
    {
        $req = [
            'version'       => self::VERSION_5_0_0,
            'encoding'      => self::ENCODING,
            'certId'        => $this->certId,
            'signMethod'    => self::SIGN_TYPE_RSA,
            'txnType'       => self::TRANSACTION_TYPES['query'],
            'txnSubType'    => self::TRANSACTION_SUB_TYPES['query'],
            'bizType'       => self::BIZ_TYPES['B2C'],
            'accessType'    => self::ACCESS_TYPE['merchant'],
            'orderId'       => $charge['trade_no'],
            'merId'         => $this->merId,
//            'queryId'       => $charge['transaction_no']
        ];
        $req = $this->signArray($req, $this->certPrivateKey);
    }

    public function notify(Charge $charge, array $notify)
    {
//        $this->verify($notify, $cert);

        return [
            'paid'           => TRUE,
            'transaction_no' => $notify['queryId'],
            'time_paid'      => strtotime($notify['txnTime']),
            'amount'         => intval($notify['settleAmt']),
            'raw_code'       => $notify['respCode']
        ];
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
        $signArray = Helper::removeNull($signArray);
        ksort($signArray);
        $signString = Helper::joinToString($signArray);

        openssl_sign(sha1($signString), $sign, $privateKey);
        $signArray['signature'] = base64_encode($sign);

        return $signArray;
    }

    protected function verify($signString, $sign, $privateKey)
    {
        if (openssl_verify($signString, base64_decode($sign), $privateKey) !== 1) {

        }
    }

    protected function formatTime($time)
    {
        if(empty($time)) {
            return null;
        }

        return date('YmdHis', strtotime($time));
    }

}