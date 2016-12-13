<?php

namespace App\Libraries\Channel;

use App\Libraries\Channel\Alipay\AlipayQR;
use App\Libraries\Channel\Alipay\AlipayWap;
use App\Libraries\Channel\Wechat\WechatQR;
use App\Models\ChannelAlipay;
use App\Models\ChannelWechat;

class Payment
{
    const CHANNEL_ALIPAY = 'alipay';
    const CHANNEL_WECHAT = 'wechat';

    const PAYMENT_QR = 'qr';
    const PAYMENT_WAP = 'wap';

    public static function make($channelName, $paymentType)
    {
        $payment = null;

        if($channelName === self::CHANNEL_ALIPAY) {
            $channelParams = ChannelAlipay::getPaymentParameters();

            if($paymentType === self::PAYMENT_WAP) {
                $payment = new AlipayWap($channelParams);
            } else if($paymentType === self::PAYMENT_QR) {
                $payment = new AlipayQR($channelParams);
            }
        } else if($channelName === self::CHANNEL_WECHAT) {
            $channelParams = ChannelWechat::getPaymentParameters();

            if($paymentType === self::PAYMENT_QR) {
                $payment = new WechatQR($channelParams);
            }
        }

        return $payment;
    }
}