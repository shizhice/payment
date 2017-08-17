<?php
/**
 * Created by PhpStorm.
 * User: shizhice
 * Date: 2017/8/16
 * Time: 下午4:39
 */

namespace Shizhice\Payment\Drives\Src\Wechat;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\Request as GuzzleHttpRequest;
class UnifiedOrder
{
    const UNIFIED_ORDER = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    public $result = false;
    public $msg = '';
    public $data = [];

    /**
     * unified order
     * @param $data
     * @return array
     */
    public function order($data)
    {
        $data['total_fee'] = (string) $data['total_fee'];

        try{
            $request = new GuzzleHttpRequest('POST', self::UNIFIED_ORDER, [
                'Content-Type' => 'text/xml; charset=UTF8'
            ],arrayToXml($data)
            );

            $client = new GuzzleHttpClient();
            $response = $client->send($request);
        }catch (\Exception $e) {
            return [
                'return_code' => 'FAIL',
                'return_msg' => '下单失败',
            ];
        }

        return xmlToArray($response->getBody());
    }
}