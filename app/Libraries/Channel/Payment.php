<?php

namespace App\Libraries\Channel;

use App\Libraries\Channel\Alipay\AlipayBase;
use App\Libraries\Channel\Alipay\AlipayQR;
use App\Libraries\Channel\Alipay\AlipayScan;
use App\Libraries\Channel\Alipay\AlipayWap;
use App\Libraries\Channel\UnionPay\UnionPayBase;
use App\Libraries\Channel\UnionPay\UnionPayWap;
use App\Libraries\Channel\Wechat\WechatBase;
use App\Libraries\Channel\Wechat\WechatPub;
use App\Libraries\Channel\Wechat\WechatQR;
use App\Libraries\Channel\Wechat\WechatScan;
use App\Models\Channel\Alipay as ChannelAlipay;
use App\Models\Channel\Wechat as ChannelWechat;
use App\Models\Channel\UnionPay as ChannelUnionPay;

class Payment
{
    const CHANNEL_ALIPAY = 'alipay';
    const CHANNEL_WECHAT = 'wechat';
    const CHANNEL_UNION_PAY = 'union_pay';

    const PAYMENT_QR = 'qr';
    const PAYMENT_SCAN = 'scan';
    const PAYMENT_WAP = 'wap';
    const PAYMENT_PUB = 'pub';

    public static function make($channelName, $paymentType = null, $isTesting = false)
    {
        $payment = null;

        if($channelName === self::CHANNEL_ALIPAY) {
            $channelParams = ChannelAlipay::getPaymentParameters();

            if($paymentType === null) {
                $payment = new AlipayBase($channelParams);
            } if($paymentType === self::PAYMENT_WAP) {
                $payment = new AlipayWap($channelParams);
            } else if($paymentType === self::PAYMENT_QR) {
                $payment = new AlipayQR($channelParams);
            } else if($paymentType === self::PAYMENT_SCAN) {
                $payment = new AlipayScan($channelParams);
            }
        } else if($channelName === self::CHANNEL_WECHAT) {
            $channelParams = ChannelWechat::getPaymentParameters();

            if($paymentType === null) {
                $payment = new WechatBase($channelParams);
            } else if($paymentType === self::PAYMENT_PUB) {
                $payment = new WechatPub($channelParams);
            } else if($paymentType === self::PAYMENT_QR) {
                $payment = new WechatQR($channelParams);
            } else if($paymentType === self::PAYMENT_SCAN) {
                $payment = new WechatScan($channelParams);
            }
        } else if($channelName === self::CHANNEL_UNION_PAY) {
            $channelParams = ChannelUnionPay::getPaymentParameters();

            if($paymentType === null) {
                $payment = new UnionPayBase($channelParams);
            } else if($paymentType === self::PAYMENT_WAP) {
                $payment = new UnionPayWap($channelParams);
            }
        }

        if(!is_null($payment) && $isTesting === true) {
            $payment->setTesting();
        }

        return $payment;
    }

    public static function makeTesting($channelName, $paymentType = null)
    {
        return static::make($channelName, $paymentType, true);
    }
}