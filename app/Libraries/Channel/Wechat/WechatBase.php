<?php

namespace App\Libraries\Channel\Wechat;

use App\Exceptions\APIException;
use App\Exceptions\BadRequestException;
use App\Libraries\Channel\Helper;
use App\Libraries\Channel\IPayment;
use App\Models\Charge;
use App\Models\Refund;

class WechatBase extends IPayment
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

    // 微信参数变量
    protected $appId;
    protected $mchId;
    protected $key;

    public function __construct($channelParams)
    {
        $this->appId = $channelParams['appid'];
        $this->mchId = $channelParams['mch_id'];
        $this->key = $channelParams['key'];

        $this->baseUrl = self::BASE_URL;

        parent::__construct();
    }

    public function setTesting()
    {
        // 微信测试参数
        $this->baseUrl = self::BASE_URL_TESTING;
        $this->key = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456';
    }

    public function query(Charge $charge)
    {
        $req = [
            'appid'             => $this->appId,
            'mch_id'            => $this->mchId,
            'nonce_str'         => $this->generateNonceString($charge['trade_no']),
        ];

        if(!empty($charge['tn'])) {
            $req['transaction_id'] = $charge['tn'];
        } else {
            $req['out_trade_no'] = $charge['trade_no'];
        }
        $req['out_trade_no'] = $charge['trade_no'];

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
            throw new APIException('渠道请求失败: ' . $res['err_code'] . '=>' . $res['err_code_des']);
        }

        if($res['trade_state'] === 'SUCCESS') {
            $charge['status'] = Charge::STATUS_SUCCEEDED;
            $charge['paid_at'] = date('Y-m-d H:i:s', strtotime($res['time_end']));
            $charge['tn'] = $res['transaction_id'];
            $charge->save();
        }

        return parent::query($charge);
    }

    public function notify(Charge $charge, array $notify)
    {
        $sign = $notify['sign'];
//        Helper::removeKeys($notify, ['sign', 'sign_type']);
//        $signString = $this->getSignContent($notify);
//        openssl_verify($signString, base64_decode($sign), $this->alipayPublicKey);

        if(empty($notify['total_fee']) || $charge['amount'] != $notify['total_fee']) {
            throw new BadRequestException('通知无效，total_fee 不一致');
        } else if(empty($notify['out_trade_no']) || $charge['trade_no'] != $notify['out_trade_no']) {
            throw new BadRequestException('通知无效，out_trade_no 不一致');
        } else if(empty($notify['appid']) || $this->appId != $notify['app_id']) {
            throw new BadRequestException('通知无效，app_id 不一致');
        }

        if($notify['return_code'] === 'SUCCESS') {
            if($notify['result_code'] === 'SUCCESS') {
                $charge['status'] = Charge::STATUS_SUCCEEDED;
                $charge['paid_at'] = date('Y-m-d H:i:s', strtotime($notify['time_end']));
                $charge['tn'] = $notify['transaction_id'];
                $charge->save();

                return $charge;
            }
        }

        throw new BadRequestException('通知无效');
    }

    public function refund(Charge $charge, Refund $refund)
    {
        $req = [
            'appid'             => $this->appId,
            'mch_id'            => $this->mchId,
            'nonce_str'         => $this->generateNonceString($charge['trade_no']),
            'transaction_id'    => $charge['tn'],
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
            'nonce_str'         => $this->generateNonceString($charge['trade_no']),
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

    protected function generateNonceString($seed)
    {
        return md5($this->appId . $this->mchId . time() . $seed);
    }

    protected function request($action, $reqData)
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

        return strtoupper(md5($signString));
    }

    protected function getUrl($action)
    {
        $url = $this->getBaseUrl() . self::ACTIONS[$action];

        return $url;
    }

    protected function formatTime($time)
    {
        if(empty($time)) {
            return null;
        }

        return date('YmdHis', strtotime($time));
    }

}