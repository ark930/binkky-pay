<?php

namespace App\Libraries\Channel\Wechat;

use App\Exceptions\APIException;
use App\Libraries\Channel\Helper;
use App\Libraries\Channel\IPayment;
use App\Libraries\HttpClient;
use App\Models\Charge;
use App\Models\Refund;

class WechatBase implements IPayment
{
    const BASE_URL = 'https://api.mch.weixin.qq.com';
    const BASE_URL_TESTING = 'https://api.mch.weixin.qq.com/sandboxnew';

    const ACTIONS = [
        'scan.pay'      => '/pay/micropay',
        'pay'           => '/pay/unifiedorder',
        'query'         => '/pay/orderquery',
        'cancel'        => '/secapi/pay/reverse',
        'refund'        => '/secapi/pay/refund',
        'refund.query'  => '/pay/refundquery',
        'bill.check'    => '/pay/downloadbill',
    ];

    const TRADE_TYPES = [
        'qr' => 'NATIVE',
        'scan' => 'MICROPAY',
        'pub' => 'JSAPI',
    ];

    protected $baseUrl;
    protected $httpClient = null;

    protected $appId;
    protected $mchId;
    protected $key;

    public function __construct($channelParams)
    {
        $this->appId = $channelParams['appid'];
        $this->mchId = $channelParams['mch_id'];
        $this->key = $channelParams['key'];

        $this->httpClient = new HttpClient();
        $this->baseUrl = self::BASE_URL;
    }

    public function charge(Charge $charge)
    {

    }

    public function query(Charge $charge)
    {
        $req = [
            'appid'             => $this->appId,
            'mch_id'            => $this->mchId,
            'sub_mch_id'        => $this->mchId,
            'nonce_str'         => $this->generateNonceString($charge['order_no']),
        ];

        if(!empty($charge['transaction_no'])) {
            $req['transaction_id'] = $charge['transaction_no'];
        } else {
            $req['out_trade_no'] = $charge['order_no'];
        }

        $req['sign'] = $this->signArray($req, $this->key);
        $reqXml = Helper::arrayToXml($req);

        $res = $this->request($this->getUrl('query'), $reqXml);

        if ($res['return_code'] != 'SUCCESS')
        {
            throw new APIException('渠道请求失败');
        }

        $this->verifyResponse($res, $this->key);
        if ($res['result_code'] != 'SUCCESS')
        {
            if ($res['err_code'] == 'OUT_TRADE_NO_USED')
            {
                throw new APIException('订单号已使用');
            }
            throw new APIException('渠道请求失败');
        }
    }

    public function notify(Charge $charge, array $notify)
    {

    }

    public function refund(Charge $charge, Refund $refund)
    {
        $req = [
            'appid'             => $this->appId,
            'mch_id'            => $this->mchId,
            'nonce_str'         => $this->generateNonceString($charge['out_trade_no']),
            'transaction_id'    => $charge['transaction_id'],
//            'out_trade_no'      => $charge['out_trade_no'],
//            'out_refund_no'     => $refund['out_refund_no'],
//            'total_fee'         => $charge['total_fee'],
//            'refund_fee'        => $refund['refund_fee'],
//            'op_user_id'        => $refund['op_user_id'],
        ];
    }

    public function refundQuery(Charge $charge, Refund $refund)
    {
        $req = [
            'appid'             => $this->appId,
            'mch_id'            => $this->mchId,
            'nonce_str'         => $this->generateNonceString($charge['out_trade_no']),
            'transaction_id'    => $charge['transaction_id'],
            'out_trade_no'      => $charge['out_trade_no'],
            'out_refund_no'     => $refund['out_refund_no'],
            'refund_id'         => $refund['refund_id'],
        ];
    }

    public function cancel(Charge $charge)
    {
        $req = [
            'appid'             => $this->appId,
            'mch_id'            => $this->mchId,
            'nonce_str'         => $this->generateNonceString($charge['out_trade_no']),
            'transaction_id'    => $charge['transaction_id'],
            'out_trade_no'      => $charge['out_trade_no'],
        ];
    }

    public function billQuery(array $params)
    {
        $req = [
            'appid'             => $this->appId,
            'mch_id'            => $this->mchId,
            'nonce_str'         => $this->generateNonceString($params['out_trade_no']),
            'bill_date'         => $params['bill_date'],
            'bill_type'         => $params['bill_type'],
        ];

        $req['sign'] = $this->signArray($req, $this->key);
        $reqXml = Helper::arrayToXml($req);

        $res = $this->request($this->getUrl('bill.check'), $reqXml);
        $res = \GuzzleHttp\json_decode($res);

        if ($res['return_code'] != 'SUCCESS')
        {
            throw new APIException('渠道请求失败');
        }

        $this->verifyResponse($res, $this->key);
    }

    public function setTesting()
    {
        $this->baseUrl = self::BASE_URL_TESTING;
        $this->key = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456';
    }

    protected function generateNonceString($seed)
    {
        return md5($this->appId . $this->mchId . time() . $seed);
    }

    protected function request($action, $reqData, $cert_array = NULL)
    {
        $res = NULL;
        for ($i = 0; $i < 2; $i++)
        {
            try
            {
                $this->httpClient->initHttpClient();
                $res = $this->httpClient->requestPlainText('POST', $action, $reqData);
            }
            catch (APIException $e)
            {
                if ($e->getMessage() == 'channel_connection_error')
                {
                    continue;
                }
            }

            $res = Helper::xmlToArray($res);

            if ($res['return_code'] != 'SUCCESS')
            {
                if ($res['return_msg'] == 'SYSTEMERROR')
                {
                    continue;
                }
                else
                {
                    throw new APIException($res['return_msg']);
                }
            }

            if ($res['result_code'] != 'SUCCESS')
            {
                if ($res['err_code'] == 'SYSTEMERROR')
                {
                    continue;
                }
            }

            return $res;
        }

        return $res;
    }

    protected function verifyResponse($res, $key)
    {
        if (!isset($res['sign']))
        {
            throw new APIException('返回数据解析错误');
        }
        $sign = $this->signArray($res, $key);

        if ($sign != $res['sign'])
        {
            throw new APIException('返回数据签名不一致');
        }
    }

    protected function signArray($req, $key)
    {
        $signArray = Helper::removeKeys($req, ['sign']);
        $signArray = Helper::removeEmpty($signArray);
        ksort($signArray);
        $signString = Helper::joinToString($signArray).'&key='.$key;

//        $signed_array = $this->normalize_req($req);
//        $signString = $this->create_query_string($signed_array).'&key='.$key;

        return strtoupper(md5($signString));
    }

    protected function getBaseUrl()
    {
        return $this->baseUrl;
    }

    protected function getUrl($action)
    {
        $url = $this->getBaseUrl() . self::ACTIONS[$action];

        return $url;
    }

    public function create_query_string($get_array, $url_encoding = FALSE, $quote = FALSE, $with_null = FALSE)
    {
        $qs = "";
        foreach ($get_array as $k => $v)
        {
            if (is_array($v))
            {
                continue;
            }
            $v = strval($v);
            if ($with_null === FALSE && $v === '')
            {
                continue;
            }
            $url_encoding === TRUE && $v = rawurlencode($v);
            $quote === TRUE && $v = '"'.$v.'"';
            $qs .= $k.'='.$v.'&';
        }

        //remove last char &
        $qs = substr($qs, 0, count($qs) - 2);

        //remove escape code
        get_magic_quotes_gpc() === TRUE && $qs = stripslashes($qs);

        return $qs;
    }

    public function normalize_req($req, $filter_array = [], $sort = TRUE, $with_null = FALSE)
    {
        $normalized_req = [];
        //filter
        foreach ($req as $k => $v)
        {
            if (in_array($k, $filter_array) || is_array($v))
            {
                continue;
            }
            $v = strval($v);
            if ($with_null === FALSE && $v === "")
            {
                continue;
            }
            $normalized_req[$k] = $v;
        }
        //sort
        $sort && ksort($normalized_req);

        return $normalized_req;
    }

    protected function getNotifyUrl()
    {
        return 'http://www.baidu.com';
    }
}