<?php

namespace Test\Libraries\Channels;

use App\Libraries\Channel\Alipay\AlipayBase;
use App\Libraries\Channel\Alipay\AlipayQR;
use App\Libraries\Channel\Alipay\AlipayScan;
use App\Libraries\Channel\Alipay\AlipayWap;
use App\Libraries\Channel\Payment;
use App\Libraries\Channel\Wechat\WechatBase;
use App\Libraries\Channel\Wechat\WechatPub;
use App\Libraries\Channel\Wechat\WechatQR;
use App\Libraries\Channel\Wechat\WechatScan;

class PaymentTest extends \TestCase
{
    public function testMake()
    {
        $this->assertInstanceOf(AlipayBase::class, Payment::make(Payment::CHANNEL_ALIPAY));
        $this->assertInstanceOf(AlipayWap::class, Payment::make(Payment::CHANNEL_ALIPAY, Payment::PAYMENT_WAP));
        $this->assertInstanceOf(AlipayQR::class, Payment::make(Payment::CHANNEL_ALIPAY, Payment::PAYMENT_QR));
        $this->assertInstanceOf(AlipayScan::class, Payment::make(Payment::CHANNEL_ALIPAY, Payment::PAYMENT_SCAN));

        $this->assertInstanceOf(WechatBase::class, Payment::make(Payment::CHANNEL_WECHAT));
        $this->assertInstanceOf(WechatPub::class, Payment::make(Payment::CHANNEL_WECHAT, Payment::PAYMENT_PUB));
        $this->assertInstanceOf(WechatQR::class, Payment::make(Payment::CHANNEL_WECHAT, Payment::PAYMENT_QR));
        $this->assertInstanceOf(WechatScan::class, Payment::make(Payment::CHANNEL_WECHAT, Payment::PAYMENT_SCAN));
    }
}