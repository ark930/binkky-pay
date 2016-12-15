<?php

namespace App\Libraries\Channel\Alipay;

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
//        'scan.pay'      => 'alipay.trade.pay',
//        'qrcode.pay'    => 'alipay.trade.precreate',
//        'wap.pay'       => 'alipay.trade.wap.pay',
        'query'         => 'alipay_trade_query_response',
//        'close'         => 'alipay.trade.close',
//        'cancel'        => 'alipay.trade.cancel',
        'refund'        => 'alipay_trade_refund_response',
        'refund.query'  => 'alipay_trade_fastpay_refund_query_response',
//        'bill.check'    => 'alipay.data.dataservice.bill.downloadurl.query',
//        'settle '       => 'alipay.trade.order.settle',
    ];

    const RESPONSE_CODE = [
        'success'           => '10000',
        'business.failed'   => '40004',
    ];

    protected $appId = null;
    protected $privateKey = null;
    protected $alipayPublicKey = null;
    protected $httpClient = null;

    public function __construct($channelParams, HttpClient $httpClient)
    {
        $this->appId = $channelParams->appid;
        $this->privateKey = $channelParams->private_key;
        $this->alipayPublicKey = $channelParams->alipay_public_key;

        $this->httpClient = $httpClient;
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

        if($data['trade_status'] === 'TRADE_FINISHED' || $data['trade_status'] === 'TRADE_SUCCESS') {
            $charge['status'] = Charge::STATUS_SUCCEEDED;
            $charge['paid_at'] = $data['send_pay_date'];
            empty($charge['transaction_no']) && $charge['transaction_no'] = $data['trade_no'];
            $charge->save();
        }

        return $data;
    }

    public function notify(Charge $charge, array $notify)
    {
        return '';
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

        $requestUrl = $this->makeRequest(self::METHODS['refund'], $bizContent);

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
        $requestUrl = "?".$preSignStr."&sign=".urlencode($sign);

        return $requestUrl;
    }

    protected function getSignContent($params) {
        ksort($params);

        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if ("@" != substr($v, 0, 1)) {

                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . ($v);
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . ($v);
                }
                $i++;
            }
        }

//        unset ($k, $v);

        return $stringToBeSigned;
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
}