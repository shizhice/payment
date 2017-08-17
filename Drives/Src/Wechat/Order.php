<?php
/**
 * Created by PhpStorm.
 * User: shizhice
 * Date: 2017/8/16
 * Time: 下午7:01
 */

namespace Shizhice\Payment\Drives\Src\Wechat;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\Request as GuzzleHttpRequest;
class Order
{
    const ORDER_QUERY_URL = 'https://api.mch.weixin.qq.com/pay/orderquery';

    const CLOSE_ORDER_URL = "https://api.mch.weixin.qq.com/pay/closeorder";

    /**
     * get order status
     * @param $data
     * @return array|mixed
     */
    public function query($data)
    {
        try{
            $request = new GuzzleHttpRequest('POST', self::ORDER_QUERY_URL, [
                    'Content-Type' => 'text/xml; charset=UTF8'
                ],arrayToXml($data)
            );

            $client = new GuzzleHttpClient();
            $response = $client->send($request);
        }catch (\Exception $e) {
            return [
                'return_code' => 'FAIL',
                'return_msg' => '查询失败',
            ];
        }

        return xmlToArray($response->getBody());
    }

    /**
     * close the order
     * @param $data
     * @return array|mixed
     */
    public function close($data)
    {
        try{
            $request = new GuzzleHttpRequest('POST', self::CLOSE_ORDER_URL, [
                    'Content-Type' => 'text/xml; charset=UTF8'
                ],arrayToXml($data));

            $client = new GuzzleHttpClient();
            $response = $client->send($request);
        }catch (\Exception $e) {
            return [
            'return_code' => 'FAIL',
            'return_msg' => '关闭订单失败',
            ];
        }

        return xmlToArray($response->getBody());
    }
}