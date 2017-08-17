<?php
if (! function_exists('only')) {

    /**
     * return only key data from array
     * @param array $input
     * @param $keys
     * @return array
     */
    function only(array $input, $keys)
    {
        $keys = is_array($keys) ? $keys : array_slice(func_get_args(),1);

        $results = [];

        foreach ($keys as $key) {
            $results[$key] = $input[$key];
        }

        return $results;
    }
}

if (! function_exists('except')) {

    /**
     * return except key data from array
     * @param array $input
     * @param $keys
     * @return array
     */
    function except(array $input, $keys)
    {
        $keys = is_array($keys) ? $keys : array_slice(func_get_args(),1);

        foreach ($keys as $key) {
            unset($input[$key]);
        }

        return $input;
    }
}

if (! function_exists('extend')) {
    function extend(array $to, array $from) {
        foreach ($from as $key => $value) {
            if (is_array($value)) {
                $to[$key] = extend((array) (isset($to[$key]) ? $to[$key] : []),$value);
            }else{
                $to[$key] = $value;
            }
        }
        return $to;
    }
}

if (! function_exists('payment')) {
    function payment(string $drive = null)
    {
        return Shizhice\Payment\Payment::drive($drive);
    }
}

if (! function_exists('str_random')) {
    function str_random($length = 32)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }
}

if (! function_exists('arrayToXml')) {
    //数组转XML
    function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
}

if (! function_exists('xmlToArray')) {
    //将XML转为array
    function xmlToArray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }
}
