<?php

namespace App\Libraries;

use App\Exceptions\APIException;

class WechatQR implements IPayment
{
    const CREATE_URL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    const TRADE_TYPE_NATIVE = 'NATIVE';

    private $ssl_verify = TRUE;
    private $timeout = 10;

    protected $appId;
    protected $mchId;
    protected $key;

    public function __construct($channelParams)
    {
        $this->appId = $channelParams->appid;
        $this->mchId = $channelParams->mch_id;
        $this->key = $channelParams->key;
    }

    public function create(array $chargeParams = [])
    {
        $req = array(
            'appid'            => $this->appId,
            'mch_id'           => $this->mchId,
            'nonce_str'        => $this->generateNonceString($chargeParams['out_trade_no']),
            'body'             => $chargeParams['body'],
            'out_trade_no'     => $chargeParams['out_trade_no'],
            'total_fee'        => $chargeParams['amount'],
            'spbill_create_ip' => $chargeParams['client_ip'],
            'time_start'       => $chargeParams['time_start'],
            'notify_url'       => $chargeParams['notify_url'],
            'trade_type'       => self::TRADE_TYPE_NATIVE,
//            'attach'           => '',
        );
        $req['product_id'] = $chargeParams['product_id'];

        $req['sign'] = $this->signArray($req, $this->key);
        $req_xml = $this->arrayToXml($req);

        $res = $this->request(self::CREATE_URL, $req_xml);

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

        if (!isset($res['code_url']))
        {
            throw new APIException('渠道返回解析失败');
        }

        return $res['code_url'];
    }

    protected function generateNonceString($seed)
    {
        return md5($this->appId . $this->mchId . time() . $seed);
    }

    protected function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val)
        {
            if (is_numeric($val))
            {
                $xml .= "<".$key.">".$val."</".$key.">";
            }
            else
            {
                $xml .= "<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml .= "</xml>";

        return $xml;
    }

    protected function request($req_url, $req_data, $cert_array = NULL)
    {
        $res = NULL;
        for ($i = 0; $i < 2; $i++)
        {
            try
            {
                $res = $this->curlPost($req_url, $req_data, $cert_array);
            }
            catch (APIException $e)
            {
                if ($e->getMessage() == 'channel_connection_error')
                {
                    continue;
                }
            }

            $res = $this->xmlToArray($res);

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

    public function curlPost($url, $data, $params = array(), $is_form = FALSE)
    {
        if(is_array($params))
        {
            $params = array_merge($params, array('ca_cert' => 'cacert.pem'));
        }
        if (($res = $this->post($url, $data, $params, $is_form)) === FALSE)
        {
            throw new APIException('channel_connection_error');
        }

        return $res;
    }

    public function post($url, $data, $params = [], $is_form = FALSE)
    {
        $curl = curl_init($url);
        $headers = [];
        $hide_header = 0;
        $post_data = $data;
        if (isset($params['header']) && !empty($params['header']))
        {
            $headers[] = $params['header'];
            $hide_header = 1;
        }
        if (isset($params['content_type']) && $params['content_type'] === 'xml')
        {
            $post_data = $data;
        }
        else if ($is_form)
        {
            $o = "";
            foreach ($data as $k => $v)
            {
                $o .= "$k=".urlencode($v)."&";
            }
            $post_data = substr($o, 0, -1);
        }
        else if (is_array($data))
        {
            // convert to json
            $post_data = json_encode($data, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT);
            $headers[] = 'Content-Type: application/json;charset=utf-8';

        }
        $timeout = isset($params['timeout']) ? $params['timeout'] : $this->timeout;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_HEADER, $hide_header); // hide header info
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // response by variable
        curl_setopt($curl, CURLOPT_POST, TRUE); // method 'POST'
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data); // input data
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); // timeout limit

        // ssl verify
        if ($this->ssl_verify && isset($params['ca_cert']))
        {
            //不走默认,微信不支持sslv1\sslv2
            curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_CAINFO, __DIR__.'/../../'.$params['ca_cert']);
        }
        else
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        }
        if (isset($params['cert']))
        {
            curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLCERT, $params['cert']);
        }
        isset($params['cert_pass']) && curl_setopt($curl, CURLOPT_SSLCERTPASSWD, $params['cert_pass']);
        if (isset($params['cert_key']))
        {
            curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLKEY, $params['cert_key']);
        }
        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }


    protected function xmlToArray($xml)
    {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), TRUE);

        return $array_data;
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
        $signed_array = $this->normalizeRequest($req, array('sign'));
        $signed_string = $this->makeSignString($signed_array).'&key='.$key;

        return strtoupper(md5($signed_string));
    }

    public function normalizeRequest($req, $filter_array = [], $sort = TRUE, $with_null = FALSE)
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

    public function makeSignString($get_array, $url_encoding = FALSE, $quote = FALSE, $with_null = FALSE)
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

}