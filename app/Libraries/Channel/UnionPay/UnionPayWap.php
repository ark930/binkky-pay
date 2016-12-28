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
            'backUrl'       => $this->makeNotifyUrl($charge['id']),
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

        $this->credential = $req;
        return parent::charge($charge);
    }
}