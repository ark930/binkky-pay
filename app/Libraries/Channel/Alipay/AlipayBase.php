<?php

namespace App\Libraries\Channel\Alipay;

use App\Libraries\Channel\Helper;
use App\Libraries\Channel\IPayment;
use App\Libraries\HttpClient;
use App\Models\Charge;
use App\Models\Refund;

class AlipayBase implements IPayment
{
    const GATEWAY_URL = "https://openapi.alipay.com/gateway.do";

    const FORMAT_JSON = 'JSON';
    const VERSION_1_0 = '1.0';
    const SIGN_TYPE_RSA = 'RSA';
    const CHARSET_UTF8 = 'UTF-8';
    const METHODS = [
        'scan.pay'      => 'alipay.trade.pay',
        'qrcode.pay'    => 'alipay.trade.precreate',
        'wap.pay'       => 'alipay.trade.wap.pay',
        'query'         => 'alipay.trade.query',
        'close'         => 'alipay.trade.close',
        'cancel'        => 'alipay.trade.cancel',
        'refund'        => 'alipay.trade.refund',
        'refund.query'  => 'alipay.trade.fastpay.refund.query',
        'bill.check'    => 'alipay.data.dataservice.bill.downloadurl.query',
        'settle '       => 'alipay.trade.order.settle',
    ];

    const RESPONSE_KEY = [
        'scan.pay'      => 'alipay_trade_pay_response',
        'qrcode.pay'    => 'alipay_trade_precreate_response',
//        'wap.pay'       => 'alipay.trade.wap.pay',
        'query'         => 'alipay_trade_query_response',
        'close'         => 'alipay_trade_close_response',
        'cancel'        => 'alipay_trade_cancel_response',
        'refund'        => 'alipay_trade_refund_response',
        'refund.query'  => 'alipay_trade_fastpay_refund_query_response',
        'bill.check'    => 'alipay_data_dataservice_bill_downloadurl_query_response',
        'settle '       => 'alipay_trade_order_settle_response',
    ];

    const RESPONSE_CODE = [
        'success'           => '10000',
        'business.failed'   => '40004',
    ];

    protected $appId = null;
    protected $privateKey = null;
    protected $alipayPublicKey = null;
    protected $httpClient = null;

    public function __construct($channelParams)
    {
        $this->appId = $channelParams['appid'];
        $this->privateKey = $channelParams['private_key'];
        $this->alipayPublicKey = $channelParams['alipay_public_key'];

        $this->httpClient = new HttpClient();
    }

    public function charge(Charge $charge)
    {
    }

    public function query(Charge $charge)
    {
        if(!empty($charge['transaction_no'])) {
            $bizContent['trade_no'] = $charge['transaction_no'];
        } else {
            $bizContent['out_trade_no'] = $charge['order_no'];
        }

        $requestUrl = $this->makeRequest(self::METHODS['query'], $bizContent);

        $this->httpClient->initHttpClient(self::GATEWAY_URL);
        $response = $this->httpClient->requestJson('GET', $requestUrl);

        $data = $this->parseResponse($response, self::RESPONSE_KEY['query']);

        if($data['code'] === self::RESPONSE_CODE['success']) {
            if($data['trade_status'] === 'TRADE_FINISHED' || $data['trade_status'] === 'TRADE_SUCCESS') {
                $charge['status'] = Charge::STATUS_SUCCEEDED;
                $charge['paid_at'] = $data['send_pay_date'];
                $charge['transaction_no'] = $data['trade_no'];
                $charge->save();
            }
        } else {
            $charge['status'] = Charge::STATUS_FAILED;
            $charge->save();
        }

        return $data;
    }

    public function notify(Charge $charge, array $notify)
    {
        $sign = $notify['sign'];
        $signType = $notify['sign_type'];
        Helper::removeKeys($notify, ['sign', 'sign_type']);
        $signString = $this->getSignContent($notify);
//        openssl_verify($signString, base64_decode($sign), $this->alipayPublicKey);

        if($charge['amount'] === $notify['total_amount']) {

        } else if($charge['order_no'] === $notify['out_trade_no']) {

        } else if($charge['app_id'] === $notify['app_id']) {

        } else if(isset($notify['seller_id']) && $charge['seller_id'] === $notify['seller_id']) {

        } else if(isset($notify['seller_email']) && $charge['seller_email'] === $notify['seller_email']) {

        }
        if($notify['trade_status'] === 'TRADE_FINISHED' || $notify['trade_status'] === 'TRADE_SUCCESS') {
            $charge['status'] = Charge::STATUS_SUCCEEDED;
            $charge['paid_at'] = $notify['gmt_payment'];
            $charge['transaction_no'] = $notify['trade_no'];
            $charge->save();
        } else if($notify['trade_status'] === 'TRADE_CLOSED') {
            $charge['status'] = Charge::STATUS_CLOSED;
            $charge['paid_at'] = $notify['gmt_payment'];
            $charge['transaction_no'] = $notify['trade_no'];
            $charge->save();
        }

        return $charge;
    }

    public function refund(Charge $charge, Refund $refund)
    {
        $bizContent = [
            'refund_amount'     => $refund['amount'],
            'refund_reason'     => $refund['description'],
            'out_request_no'    => $refund['order_no'],
//            'operator_id'       => $refund['operator_id'],
//            'store_id'          => $refund['store_id'],
//            'terminal_id'       => $refund['terminal_id'],
        ];

        if(!empty($charge['transaction_no'])) {
            $bizContent['trade_no'] = $charge['transaction_no'];
        } else {
            $bizContent['out_trade_no'] = $charge['order_no'];
        }

        $requestUrl = $this->makeRequest(self::METHODS['refund'], $bizContent, $refund['created_at']);

        $this->httpClient->initHttpClient(self::GATEWAY_URL);
        $response = $this->httpClient->requestJson('GET', $requestUrl);

        $data = $this->parseResponse($response, self::RESPONSE_KEY['refund']);

        return $data;
    }

    public function refundQuery(Charge $charge, Refund $refund)
    {
        $bizContent['out_request_no'] = $charge['order_no'];

        if(!empty($charge['transaction_no'])) {
            $bizContent['trade_no'] = $charge['transaction_no'];
        } else {
            $bizContent['out_trade_no'] = $charge['order_no'];
        }

        $requestUrl = $this->makeRequest(self::METHODS['refund.query'], $bizContent);

        $this->httpClient->initHttpClient(self::GATEWAY_URL);
        $response = $this->httpClient->requestJson('GET', $requestUrl);

        $data = $this->parseResponse($response, self::RESPONSE_KEY['refund.query']);

        return $data;
    }

    public function close(Charge $charge)
    {
        $bizContent['operator_id'] = $charge['operator_id'];

        if(!empty($charge['transaction_no'])) {
            $bizContent['trade_no'] = $charge['transaction_no'];
        } else {
            $bizContent['out_trade_no'] = $charge['order_no'];
        }

        $requestUrl = $this->makeRequest(self::METHODS['close'], $bizContent);

        return $requestUrl;
    }

    public function cancel(Charge $charge)
    {
        if(!empty($charge['transaction_no'])) {
            $bizContent['trade_no'] = $charge['transaction_no'];
        } else {
            $bizContent['out_trade_no'] = $charge['order_no'];
        }

        $requestUrl = $this->makeRequest(self::METHODS['cancel'], $bizContent);

        return $requestUrl;
    }

    public function settle(Charge $charge)
    {
        $bizContent = [
            'royalty_parameters'=> $charge['royalty_parameters'],
            'operator_id'       => $charge['operator_id'],
        ];

        if(!empty($charge['transaction_no'])) {
            $bizContent['trade_no'] = $charge['transaction_no'];
        } else {
            $bizContent['out_trade_no'] = $charge['order_no'];
        }

        $requestUrl = $this->makeRequest(self::METHODS['cancel'], $bizContent);

        return $requestUrl;
    }

    public function billQuery(array $params)
    {
        $bizContent = [
            'bill_type'         => $params['bill_type'],
            'bill_date'         => $params['bill_date'],
        ];

        $requestUrl = $this->makeRequest(self::METHODS['bill.check'], $bizContent);

        $this->initHttpClient(self::GATEWAY_URL);
        $body = $this->requestForm('GET', $requestUrl);
        $body = \GuzzleHttp\json_decode($body);

        $sign = $body['sign'];
        $preSignStr = $this->getSignContent($body['alipay_data_dataservice_bill_downloadurl_query_response']);

        $ret = $this->verify($preSignStr, $sign, $this->alipayPublicKey);

        return $body['alipay_data_dataservice_bill_downloadurl_query_response']['bill_download_url'];
    }

    protected function makeRequest($method, $bizContent, $timestamp = null)
    {
        if(is_null($timestamp)) {
            $timestamp = date('Y-m-d H:i:s');
        }
        $commonParams = $this->makeCommonParameters($method, $timestamp);
        $commonParams['biz_content'] = json_encode($bizContent);

        $requestUrl = $this->makeRequestUrl($commonParams);

        return $requestUrl;
    }

    protected function makeCommonParameters($method, $timestamp)
    {
        $commonParameters = [
            'app_id'        => $this->appId,
            'method'        => $method,
            'format'        => self::FORMAT_JSON,
            'charset'       => self::CHARSET_UTF8,
            'sign_type'     => self::SIGN_TYPE_RSA,
            'timestamp'     => $timestamp,
            'version'       => self::VERSION_1_0,
        ];

        return $commonParameters;
    }

    protected function makeRequestUrl($params)
    {
        $preSignStr = $this->getSignContent($params);
        $sign =  $this->sign($preSignStr, $this->privateKey);
        $requestUrl = self::GATEWAY_URL."?".$preSignStr."&sign=".urlencode($sign);

        return $requestUrl;
    }

    protected function getSignContent($params)
    {
        $params = Helper::removeEmpty($params);
        ksort($params);

        return Helper::joinToString($params);
    }

    protected function sign($data, $privateKey) {
        openssl_sign($data, $sign, $privateKey);
        $sign = base64_encode($sign);

        return $sign;
    }

    protected function parseResponse($response, $key)
    {
        $response = \GuzzleHttp\json_decode($response, true);

        $sign = $response['sign'];
        $data = $response[$key];

        if($this->verify($data, $sign, $this->alipayPublicKey) === false) {
            // TODO throw exception
        }

        if($data['code'] === self::RESPONSE_CODE['success']) {

        }

        return $data;
    }

    protected function verify($data, $sign, $alipayPublicKey) {
        $signString = $this->getSignContent($data);
//        if(openssl_verify($signString, base64_decode($sign), $alipayPublicKey) === 1) {
//            return true;
//        }
        return false;
    }

    protected function formatAmount($amount)
    {
        return $amount / 100;
    }

    protected function makeNotifyUrl($charge_id)
    {
        return '';
    }

    protected function makeExpiredTime($second)
    {
        return '90m';
    }
}