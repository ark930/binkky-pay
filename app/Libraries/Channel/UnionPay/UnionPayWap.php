<?php

namespace App\Libraries\Channel\UnionPay;

use App\Models\Charge;

class UnionPayWap extends UnionPayBase
{
    public function charge(Charge $charge)
    {
        $req = [
            'version'       => self::VERSION_5_0_0,
            'encoding'      => self::ENCODING,
            'certId'        => $this->certId,
            'signMethod'    => self::SIGN_TYPE_RSA,
            'txnType'       => self::TRANSACTION_TYPES['sale'],
            'txnSubType'    => self::TRANSACTION_SUB_TYPES['sale'],
            'bizType'       => self::BIZ_TYPES['B2C'],
            'channelType'   => self::CHANNEL_TYPES['mobile'],
            'backUrl'       => $charge['notify_url'],
            'accessType'    => self::ACCESS_TYPE['merchant'],
            'merId'         => $this->merId,
            'orderId'       => $charge['trade_no'],
            'txnTime'       => $this->formatTime($charge['created_at']),
            'txnAmt'        => $charge['amount'],
            'currencyCode'  => self::CURRENCY_CODES['cny'],
            'customerIp'    => $charge['client_ip'],
            'orderDesc'     => $charge['title'],
            'reqReserved'   => $charge['id'],
        ];
        isset($ch['time_expire']) && $req['payTimeout'] = $this->formatTime($charge['expired_at']);

        $req['frontUrl'] = 'http://www.baidu.com';

        $req = $this->signArray($req, $this->certPrivateKey);

//        $html = $this->createAutoFormHtml($req, $this->getBaseUrl().self::ACTIONS['wap.pay']);
//        $this->credential = $html;
//        return $html;
        $this->credential = $req;
        return parent::charge($charge);
    }

    private function createAutoFormHtml($params, $reqUrl) {
        // <body onload="javascript:document.pay_form.submit();">
        $encodeType = isset ( $params ['encoding'] ) ? $params ['encoding'] : 'UTF-8';
        $html = <<<eot
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset={$encodeType}" />
</head>
<body onload="javascript:document.pay_form.submit();">
    <form id="pay_form" name="pay_form" action="{$reqUrl}" method="post">
eot;
        foreach ( $params as $key => $value ) {
            $html .= "    <input type=\"hidden\" name=\"{$key}\" id=\"{$key}\" value=\"{$value}\" />\n";
        }
        $html .= <<<eot
   <!-- <input type="submit" type="hidden">-->
    </form>
</body>
</html>
eot;
        return $html;
    }
}