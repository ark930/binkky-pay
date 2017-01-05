<?php

namespace App\Libraries\Channel;

use App\Libraries\HttpClient;
use App\Models\Charge;
use App\Models\Refund;

abstract class IPayment
{
    /**
     * @var \App\Libraries\HttpClient
     */
    protected $httpClient;

    /**
     * @var string 第三方渠道请求地址
     */
    protected $baseUrl;

    /**
     * @var string 第三方渠道返回的支付凭据
     */
    protected $credential;

    public function __construct()
    {
        $this->httpClient = new HttpClient();
    }

    /**
     * 以测试模式向第三方支付渠道发起请求
     */
    abstract public function setTesting();

    /**
     * 查询
     * @param Charge $charge
     * @return mixed
     */
    public function query(Charge $charge)
    {
        return [
            'charge' => Charge::find($charge['id']),
        ];
    }

    /**
     * 异步通知
     * @param Charge $charge
     * @param array $notify
     * @return mixed
     */
    abstract public function notify(Charge $charge, array $notify);

    /**
     * 退款
     * @param Charge $charge
     * @param Refund $refund
     * @return mixed
     */
    abstract public function refund(Charge $charge, Refund $refund);

    /**
     * 退款查询
     * @param Charge $charge
     * @param Refund $refund
     * @return mixed
     */
    abstract public function refundQuery(Charge $charge, Refund $refund);

    /**
     * 支付请求
     * @param Charge $charge
     * @return array
     */
    public function charge(Charge $charge)
    {
        return $this->formatChargeCredential($charge['id'], $this->credential);
    }

    protected function getBaseUrl()
    {
        return $this->baseUrl;
    }

    protected function formatChargeCredential($chargeId, $credential)
    {
        return [
            'credential' => $credential,
            'charge' => Charge::find($chargeId),
        ];
    }

//    protected function getNotifyUrl()
//    {
//        return env('NOTIFY_BASE_URL') . '/charges/%s/notify';
//    }
}