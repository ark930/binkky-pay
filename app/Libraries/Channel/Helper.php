<?php

namespace App\Libraries\Channel;

class Helper
{
    /**
     * 将数组中不需要的 key 移除
     * @param array $data
     * @param array $keys
     * @return array
     */
    public static function removeKeys(array $data, array $keys)
    {
        foreach ($keys as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    /**
     * 将数组中的空值移除
     * @param array $data
     * @return array
     */
    public static function removeEmpty(array $data)
    {
        foreach ($data as $k => $v) {
            if(empty($v)) {
                unset($data[$k]);
            }
        }

        return $data;
    }

    /**
     * 将数组转换为键值对字符串
     * @param array $data
     * @return string
     */
    public static function joinToString(array $data)
    {
        $string = '';
        $i = 0;
        foreach ($data as $k => $v) {
            $string .= ($i++ === 0) ? "$k=$v" : "&$k=$v";
        }

        return $string;
    }

    public static function xmlToArray($xml)
    {
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), TRUE);

        return $array_data;
    }


    public static function arrayToXml($arr)
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
}