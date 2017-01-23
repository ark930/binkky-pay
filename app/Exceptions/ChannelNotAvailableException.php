<?php

namespace App\Exceptions;

use Exception;

class ChannelNotAvailableException extends Exception
{
    protected $error = '';
    public function __construct($channelName)
    {
        $this->error = 'channel_not_available';
        parent::__construct("支付渠道 $channelName 未开通", 400);
    }

    public function getError()
    {
        return $this->error;
    }
}