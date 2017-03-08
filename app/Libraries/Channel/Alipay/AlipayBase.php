<?php

namespace App\Libraries\Channel\Alipay;

use App\Exceptions\APIException;
use App\Exceptions\BadRequestException;
use App\Libraries\Channel\Helper;
use App\Libraries\Channel\IPayment;
use App\Models\Charge;
use App\Models\Refund;
use Carbon\Carbon;

class AlipayBase extends IPayment
{
    const GATEWAY_URL = "https://openapi.alipay.com/gateway.do";
    const GATEWAY_URL_TESTING = "https://openapi.alipaydev.com/gateway.do";

    const FORMAT_JSON = 'JSON';
    const VERSION_1_0 = '1.0';
    const SIGN_TYPE_RSA = 'RSA';
    const CHARSET_UTF8 = 'UTF-8';
    const ACTIONS = [
        'app.pay'       => 'alipay.trade.app.pay',
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

    // 支付宝参数变量
    protected $appId = null;
    protected $privateKey = null;
    protected $alipayPublicKey = null;

    public function __construct($channelParams)
    {
        $this->appId = $channelParams['appid'];
        $this->privateKey = $channelParams['private_key'];
        $this->alipayPublicKey = $channelParams['alipay_public_key'];

        $this->baseUrl = self::GATEWAY_URL;

        parent::__construct();
    }

    public function setTesting()
    {
        $this->baseUrl = self::GATEWAY_URL_TESTING;

        // 支付宝沙箱测试应用参数
        $this->appId = '2016072900113901';
        $this->alipayPublicKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDIgHnOn7LLILlKETd6BFRJ0Gqg
S2Y3mn1wMQmyh9zEyWlz5p1zrahRahbXAfCfSqshSNfqOmAQzSHRVjCqjsAw1jyq
rXaPdKBmr90DIpIxmIyKXv4GGAkPyJ/6FTFY99uhpiq0qadD/uSzQsefWo0aTvP/
65zi3eof7TcZ32oWpwIDAQAB
-----END PUBLIC KEY-----';
        $this->privateKey = '-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQDt1x9PyhXDcIKMZWqOKjHu/HrD/2naqv0LxaiD6FlZWknT215E
YxxRgGwPLIxkMK6T/XLhMCK5pffnz/ORCgI3bwFNZpIjqQTEieZ6+H3jCY3TjNvL
ZO05b4LlXRdi68Lmtu4Ylu5zowd/lM3ZeZ3NnSJr6fcAm8lh43f0npEnyQIDAQAB
AoGBAKwkiassbvgX1MVdGfRvodiBsTFvCeSU4bXFiCSA5TqA2PKX0fDZc0OiGZQa
ADr76T9/r8hBGMEZ2QZVQsr1XYfwdymFwduTCiK6KNtUNSTt4xx8Vjef2npNDq5v
bLyNtW6BpqjpsqLb0ltnJGhIPubHQv7YRhXkjQcWlw1Uj6IBAkEA+wva+Po4DQmO
3khP/Nb8wyxSQOiu/4W6pjkxvLgRwzdcR6NOSOk55fENP4TIrYO3NsPpaUTx2E3X
wwhCWIVdqQJBAPKIjsdhfXaJ5WTrdI9BsHjq/EvyI8HX07cuX8dMvaKXoPo5u5c3
cl86TuvBCFpWj3mE0+ScLWz/G+EhCpS6jSECQQCsW6hcVlaTROOs4xLfsa7aRRy3
6cj0MBlEtHHccrfnQqP9nzZJQq74mvYQjRbGYm8wj3M6ThaI/nDLO2lpoy75AkBL
Vdmr2v+Cw6CqsWyaVxg+5xcJbCRpQOY1n0UG/jJlf93z+9zmQsXXCKCdIG+8x+h3
IahD+bMuiSuayY2k1zGhAkAec+NXdmO8GKxQeAag3wUcko6y8TwMzhVHuj/FrUl1
9bDupWK8x5kIqDgR4MPNfoZXfWC9pUiGFDjRqNNtE0In
-----END RSA PRIVATE KEY-----';
    }

    public function query(Charge $charge)
    {
        if(!empty($charge['tn'])) {
            $bizContent['trade_no'] = $charge['tn'];
        } else {
            $bizContent['out_trade_no'] = $charge['trade_no'];
        }

        $commonParams = $this->makeCommonParameters($this->getAction('query'), date('Y-m-d H:i:s'));
        $commonParams['biz_content'] = json_encode($bizContent);
        $response = $this->request($commonParams);
        $res = $this->parseResponse($response, $this->getResponseKey('query'));

        if($res['code'] === self::RESPONSE_CODE['success']) {
            if($res['trade_status'] === 'TRADE_FINISHED' || $res['trade_status'] === 'TRADE_SUCCESS') {
                $charge['status'] = Charge::STATUS_SUCCEEDED;
                $charge['paid_at'] = $res['send_pay_date'];
                $charge['tn'] = $res['trade_no'];
                $charge->save();
            }
        }

        return parent::query($charge);
    }

    public function notify(Charge $charge, array $notify)
    {
        $sign = $notify['sign'];
        $signType = $notify['sign_type'];
        Helper::removeKeys($notify, ['sign', 'sign_type']);
        $signString = $this->getSignContent($notify);
//        openssl_verify($signString, base64_decode($sign), $this->alipayPublicKey);

        if(empty($notify['total_amount']) || $charge['amount'] != $notify['total_amount']*100) {
            throw new BadRequestException('通知无效，total_amount 不一致');
        } else if(empty($notify['out_trade_no']) || $charge['trade_no'] != $notify['out_trade_no']) {
            throw new BadRequestException('通知无效，out_trade_no 不一致');
        } else if(empty($notify['app_id']) || $this->appId != $notify['app_id']) {
            throw new BadRequestException('通知无效，app_id 不一致');
        }

        if($notify['trade_status'] === 'TRADE_FINISHED' || $notify['trade_status'] === 'TRADE_SUCCESS') {
            $charge['status'] = Charge::STATUS_SUCCEEDED;
            $charge['paid_at'] = $notify['gmt_payment'];
            $charge['tn'] = $notify['trade_no'];
            $charge['payer_id'] = isset($notify['buyer_id']) ? $notify['buyer_id'] : '' ;
            $charge->save();
        } else if($notify['trade_status'] === 'TRADE_CLOSED') {
            $charge['status'] = Charge::STATUS_CLOSED;
            $charge['paid_at'] = $notify['gmt_payment'];
            $charge['tn'] = $notify['trade_no'];
            $charge['payer_id'] = isset($notify['buyer_id']) ? $notify['buyer_id'] : '' ;
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

        if(!empty($charge['tn'])) {
            $bizContent['trade_no'] = $charge['tn'];
        } else {
            $bizContent['out_trade_no'] = $charge['trade_no'];
        }

        $requestUrl = $this->makeRequest($this->getAction('refund'), $bizContent, $refund['created_at']);

        $this->httpClient->initHttpClient($this->getBaseUrl());
        $response = $this->httpClient->requestJson('GET', $requestUrl);

        $data = $this->parseResponse($response, $this->getResponseKey('refund'));

        return $data;
    }

    public function refundQuery(Charge $charge, Refund $refund)
    {
        $bizContent['out_request_no'] = $charge['trade_no'];

        if(!empty($charge['tn'])) {
            $bizContent['trade_no'] = $charge['tn'];
        } else {
            $bizContent['out_trade_no'] = $charge['trade_no'];
        }

        $requestUrl = $this->makeRequest($this->getAction('refund.query'), $bizContent);

        $this->httpClient->initHttpClient($this->getBaseUrl());
        $response = $this->httpClient->requestJson('GET', $requestUrl);

        $data = $this->parseResponse($response, $this->getResponseKey('refund.query'));

        return $data;
    }

    public function close(Charge $charge)
    {
        $bizContent['operator_id'] = $charge['operator_id'];

        if(!empty($charge['tn'])) {
            $bizContent['trade_no'] = $charge['tn'];
        } else {
            $bizContent['out_trade_no'] = $charge['trade_no'];
        }

        $requestUrl = $this->makeRequest($this->getAction('close'), $bizContent);

        return $requestUrl;
    }

    public function cancel(Charge $charge)
    {
        if(!empty($charge['tn'])) {
            $bizContent['trade_no'] = $charge['tn'];
        } else {
            $bizContent['out_trade_no'] = $charge['trade_no'];
        }

        $requestUrl = $this->makeRequest($this->getAction('cancel'), $bizContent);

        return $requestUrl;
    }

    public function settle(Charge $charge)
    {
        $bizContent = [
            'royalty_parameters'=> $charge['royalty_parameters'],
            'operator_id'       => $charge['operator_id'],
        ];

        if(!empty($charge['tn'])) {
            $bizContent['trade_no'] = $charge['tn'];
        } else {
            $bizContent['out_trade_no'] = $charge['trade_no'];
        }

        $requestUrl = $this->makeRequest($this->getAction('cancel'), $bizContent);

        return $requestUrl;
    }

    public function billQuery(array $params)
    {
        $bizContent = [
            'bill_type'         => $params['bill_type'],
            'bill_date'         => $params['bill_date'],
        ];

        $requestUrl = $this->makeRequest($this->getAction('bill.check'), $bizContent);

        $this->initHttpClient($this->getBaseUrl());
        $body = $this->requestForm('GET', $requestUrl);
        $body = \GuzzleHttp\json_decode($body);

        $sign = $body['sign'];
        $preSignStr = $this->getSignContent($body['alipay_data_dataservice_bill_downloadurl_query_response']);

        $ret = $this->verify($preSignStr, $sign, $this->alipayPublicKey);

        return $body['alipay_data_dataservice_bill_downloadurl_query_response']['bill_download_url'];
    }

    protected function request($params)
    {
        $requestUrl = $this->makeRequestUrl($params);

        $this->httpClient->initHttpClient();
        $res = $this->httpClient->requestPlainText('GET', $requestUrl);
        $res = \GuzzleHttp\json_decode($res, true);

        return $res;
    }

    protected function makeRequest($action, $bizContent, $timestamp = null)
    {
        if(is_null($timestamp)) {
            $timestamp = date('Y-m-d H:i:s');
        }
        $commonParams = $this->makeCommonParameters($action, $timestamp);
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
        $requestUrl = $this->getBaseUrl()."?".$preSignStr."&sign=".urlencode($sign);

        return $requestUrl;
    }

    protected function getSignContent($params)
    {
        $params = Helper::removeEmpty($params);
        ksort($params);

        $string = Helper::joinToString($params);
        if(get_magic_quotes_gpc()){$string = stripslashes($string);}
        return $string;
    }

    protected function getSignContentWithUrlEncode($params)
    {
        $params = Helper::removeEmpty($params);
        ksort($params);

        $string = Helper::joinToStringWithUrlEncode($params);
        return $string;
    }

    protected function sign($data, $privateKey) {
        openssl_sign($data, $sign, $privateKey);
        $sign = base64_encode($sign);

        return $sign;
    }

    protected function parseResponse($response, $key)
    {
        if(empty($response['sign'])) {
            throw new APIException('返回数据解析错误');
        }

        $sign = $response['sign'];
        $data = $response[$key];

        $signString = json_encode($data, JSON_UNESCAPED_UNICODE);

        if($this->verify($signString, $sign, $this->alipayPublicKey) === false) {
            throw new APIException('返回数据签名验证失败');
        }

        if($data['code'] != '10000') {
            throw new BadRequestException('请求失败:' . $data['code'] . ' => ' . $data['sub_msg']);
        }

        return $data;
    }

    protected function verify($signString, $sign, $alipayPublicKey)
    {
        $pk = openssl_get_publickey($alipayPublicKey);

        if(openssl_verify($signString, base64_decode($sign), $pk) === 1) {
            openssl_free_key($pk);

            return true;
        }
        openssl_free_key($pk);

        return false;
    }

    protected function formatAmount($amount)
    {
        return $amount / 100;
    }

    protected function makeExpiredTime(Charge $charge)
    {
        $minutes = Carbon::parse($charge['expired_at'])->diffInMinutes($charge['created_at']);

        if($minutes == 0) {
            $minutes = 1;
        } else if($minutes > 60) {
            $hours = Carbon::parse($charge['expired_at'])->diffInHours($charge['created_at']);
            if($hours > 24) {
                $days = Carbon::parse($charge['expired_at'])->diffInDays($charge['created_at']);

                if($days > 15) {
                    $days = 15;
                }
                return $days . 'd';
            }

            return $hours . 'h';
        }

        return $minutes . 'm';
    }

    protected function getAction($name)
    {
        return self::ACTIONS[$name];
    }

    protected function getResponseKey($name)
    {
        return self::RESPONSE_KEY[$name];
    }
}