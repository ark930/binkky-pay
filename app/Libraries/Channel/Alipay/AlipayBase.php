<?php

namespace App\Libraries\Channel\Alipay;

use App\Libraries\Channel\IPayment;
use App\Libraries\HttpClientTrait;

class AlipayBase implements IPayment
{
    use HttpClientTrait;

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

    protected $appId = null;
    protected $privateKey = null;
    protected $alipayPublicKey = null;

    public function __construct($channelParams)
    {
        $this->appId = $channelParams->appid;
        $this->privateKey = $channelParams->private_key;
        $this->alipayPublicKey = $channelParams->alipay_public_key;
    }

    public function charge(array $chargeParams)
    {
    }

    public function query(array $chargeParams)
    {
        $bizContent = [
            'out_trade_no'      => $chargeParams['out_trade_no'],
            'trade_no'          => $chargeParams['trade_no'],
        ];

        $requestUrl = $this->makeRequest(self::METHODS['query'], $chargeParams['timestamp'], $bizContent);

        return $requestUrl;
    }

    public function refund(array $chargeParams, array $refundParams)
    {
        $bizContent = [
            'out_trade_no'      => $chargeParams['out_trade_no'],
            'trade_no'          => $chargeParams['trade_no'],
            'refund_amount'     => $refundParams['refund_amount'],
            'refund_reason'     => $refundParams['refund_reason'],
            'out_request_no'    => $refundParams['out_request_no'],
            'operator_id'       => $refundParams['operator_id'],
            'terminal_id'       => $refundParams['terminal_id'],
        ];

        $requestUrl = $this->makeRequest(self::METHODS['refund'], $chargeParams['timestamp'], $bizContent);

        return $requestUrl;
    }

    public function refundQuery(array $chargeParams, array $refundParams)
    {
        $bizContent = [
            'out_trade_no'      => $chargeParams['out_trade_no'],
            'trade_no'          => $chargeParams['trade_no'],
            'out_request_no'    => $refundParams['out_request_no'],
        ];

        $requestUrl = $this->makeRequest(self::METHODS['refund.query'], $chargeParams['timestamp'], $bizContent);

        return $requestUrl;
    }

    public function close(array $chargeParams)
    {
        $bizContent = [
            'out_trade_no'      => $chargeParams['out_trade_no'],
            'trade_no'          => $chargeParams['trade_no'],
            'operator_id'       => $chargeParams['operator_id'],
        ];

        $requestUrl = $this->makeRequest(self::METHODS['close'], $chargeParams['timestamp'], $bizContent);

        return $requestUrl;
    }

    public function cancel(array $chargeParams)
    {
        $bizContent = [
            'out_trade_no'      => $chargeParams['out_trade_no'],
            'trade_no'          => $chargeParams['trade_no'],
        ];

        $requestUrl = $this->makeRequest(self::METHODS['cancel'], $chargeParams['timestamp'], $bizContent);

        return $requestUrl;
    }

    public function settle(array $chargeParams)
    {
        $bizContent = [
            'out_trade_no'      => $chargeParams['out_trade_no'],
            'trade_no'          => $chargeParams['trade_no'],
            'royalty_parameters'=> $chargeParams['royalty_parameters'],
            'operator_id'       => $chargeParams['operator_id'],
        ];

        $requestUrl = $this->makeRequest(self::METHODS['cancel'], $chargeParams['timestamp'], $bizContent);

        return $requestUrl;
    }

    public function billQuery(array $params)
    {
        $bizContent = [
            'bill_type'         => $params['bill_type'],
            'bill_date'         => $params['bill_date'],
        ];

        $requestUrl = $this->makeRequest(self::METHODS['bill.check'], $params['timestamp'], $bizContent);

        $this->initHttpClient(self::GATEWAY_URL);
        $body = $this->requestForm('GET', $requestUrl);

        $sign = $body['sign'];
        $preSignStr = $this->getSignContent($body['alipay_data_dataservice_bill_downloadurl_query_response']);

        $ret = $this->verify($preSignStr, $sign, $this->alipayPublicKey);

        return $body['alipay_data_dataservice_bill_downloadurl_query_response']['bill_download_url'];
    }

    protected function makeRequest($method, $timestamp, $bizContent)
    {
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
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
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

    protected function verify($data, $sign, $alipayPublicKey) {
        if(openssl_verify($data, base64_decode($sign), $alipayPublicKey) === 1) {
            return true;
        }

        return false;
    }
}