<?php

class ChargeTest extends TestCase
{
    public function testCreate()
    {
        $this->json('POST', '/v1/charges')
            ->seeStatusCode(200);
    }

    public function testRetrieve()
    {
        $this->json('GET', '/v1/charges/1')
            ->seeStatusCode(200);
    }

    public function testNotify()
    {
        $this->json('GET', '/v1/notify/charges/1')
            ->seeStatusCode(200);
    }
}