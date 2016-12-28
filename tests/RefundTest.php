<?php

class RefundTest extends TestCase
{
    public function testCreate()
    {
        $this->json('POST', '/v1/refunds', ['charge' => 1])
            ->seeStatusCode(200);
    }

    public function testQuery()
    {
        $this->json('GET', '/v1/refunds/1')
            ->seeStatusCode(200);
    }

    public function testNotify()
    {
        $this->json('GET', '/v1/notify/refunds/1')
            ->seeStatusCode(200);
    }
}