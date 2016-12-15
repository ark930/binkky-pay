<?php

namespace Test\Libraries\Channels;

use App\Libraries\Channel\Helper;

class HelperTest extends \TestCase
{

    public function testRemoveKeys()
    {
        $data = [
            'key1' => 'val1',
            'key2' => 'val2',
        ];

        $data = Helper::removeKeys($data, ['key', 'key1']);

        $this->assertFalse(isset($data['key1']));
        $this->assertTrue(isset($data['key2']));
        $this->assertEquals($data['key2'], 'val2');
    }

    public function testRemoveEmpty()
    {
        $data = [
            'key1' => 'val1',
            'key2' => null,
            'key3' => '',
        ];

        $data = Helper::removeEmpty($data);

        $this->assertTrue(isset($data['key1']));
        $this->assertEquals($data['key1'], 'val1');
        $this->assertFalse(isset($data['key2']));
        $this->assertFalse(isset($data['key3']));
    }

    public function testJoinToString()
    {
        $data = [
            'key1' => 'val1',
            'key2' => 'val2 1',
            'key3' => 'val2_$%^',
        ];

        $string = Helper::joinToString($data);

        $this->assertEquals($string, 'key1=val1&key2=val2 1&key3=val2_$%^');
    }
}