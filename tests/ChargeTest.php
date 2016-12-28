<?php

class ChargeTest extends TestCase
{
    public function testCreate()
    {
        $this->json('POST', '/v1/charges')
            ->seeStatusCode(200);
    }

    public function testQuery()
    {
        $httpClientStub = $this->createMock(\App\Libraries\HttpClient::class);
        $httpClientStub->method('requestJson')
            ->willReturn('{"alipay_trade_query_response":{"code":"10000","msg":"Success","buyer_logon_id":"wuw***@qiaobutang.com","buyer_pay_amount":"0.00","buyer_user_id":"2088801872064307","invoice_amount":"0.00","open_id":"20881034980415798323073523019830","out_trade_no":"20160801140816608561","point_amount":"0.00","receipt_amount":"0.00","send_pay_date":"2016-08-01 14:08:56","total_amount":"40.00","trade_no":"2016080121001004300230578988","trade_status":"TRADE_FINISHED"},"sign":"h3eALOXPgAMi4hXAapN/zoYCz0JZ42ZpCNN+F9Ut3x2FRC3UWy+13u+uDUdDSMvxBspSZFNuLYhYr1c8PilfxESqsN11gYH/FcG6TPGIBO5E+6khZStUFuICwZ4RkGbwfP8fYXRTAoDv5c6hEnTUTqMRw5XeSTg7WV97akT1fJw="}');

        $channelParams = \App\Models\ChannelAlipay::getPaymentParameters();
        $charge = \App\Models\Charge::findOrFail(1);

        $payment = new \App\Libraries\Channel\Alipay\AlipayBase($channelParams, $httpClientStub);
        $chargeParams = $payment->query($charge);
//        $this->json('GET', '/v1/charges/1')
//            ->seeStatusCode(200);
    }

    public function testNotify()
    {
        $this->json('GET', '/v1/notify/charges/1')
            ->seeStatusCode(200);
    }
}