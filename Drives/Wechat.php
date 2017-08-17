<?php
/**
 * Created by PhpStorm.
 * User: shizhice
 * Date: 2017/8/16
 * Time: 下午3:14
 */

namespace Shizhice\Payment\Drives;

use Shizhice\Payment\Drives\Src\Wechat\Order;
use Shizhice\Payment\Drives\Src\Wechat\UnifiedOrder;
use Shizhice\Payment\OrderLog;
use Shizhice\Payment\PayDriveInterface;

class Wechat implements PayDriveInterface
{
    private $payOption = [];
    private $wechatKey;

    private $unifiedOrderStatus;
    private $unifiedOrderResponseMsg;
    private $successResponse;
    private $log;

    const UNIFIED_ORDER = 'UNIFIED_ORDER';
    const ORDER_QUERY = 'ORDER_QUERY';
    const ORDER_CANCEL = 'ORDER_CANCEL';

    /**
     * Wechat constructor.
     * @param array $option
     */
    public function __construct(array $option, $logHandle)
    {
        $this->payOption['appid'] = $option['appid'];
        $this->payOption['mch_id'] = $option['mch_id'];
        $this->payOption['notify_url'] = $option['notify_url'];

        $this->wechatKey = $option['key'];
        $this->log = $logHandle;
    }

    /**
     * 统一下单
     * @return mixed
     */
    public function unifiedOrder($item)
    {
        $unifiedOrder = new UnifiedOrder;
        $result = $unifiedOrder->order($this->makeOrderParam(
            $item,
            self::UNIFIED_ORDER,
            'NATIVE',
            isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1')
        );

        if ($result['return_code'] != 'SUCCESS') {

            $this->log->warning('下单失败,订单号：'.$item['out_trade_no']);
            return $this->setUnifiedOrderStatus(false, $result['return_msg'], $result);
        }elseif (! $this->checkSign($result)) {
            return $this->setUnifiedOrderStatus(false, '签名错误');
        }

        $this->log->info('下单成功,订单号：'.$item['out_trade_no'], $result);
        return $this->setUnifiedOrderStatus(true, '下单成功');
    }

    /**
     * 当面付
     * @return mixed
     */
    public function native($item)
    {
        //TODO
    }

    /**
     * js api params
     * @return mixed
     */
    public function jsApiPay($item)
    {
        //TODO
    }

    /**
     * app pay params
     * @return mixed
     */
    public function appPay($item)
    {
        //TODO
    }

    /**
     * get order param
     * @param $params
     * @return array
     */
    private function makeOrderParam($params, $orderHandleType, $tradeType = null, $ip = null)
    {

        $queryParam = [
            'out_trade_no' => $params['out_trade_no'],
            'nonce_str' => str_random(),
        ];

        if ($orderHandleType == self::UNIFIED_ORDER) {
            $queryParam = array_merge($this->payOption, $queryParam, [
                'body' => $params['body'],
                'total_fee' => $params['total_fee'] * 100,
                'trade_type' => $tradeType,
                'spbill_create_ip' => $ip,
                'product_id' => isset($params['product_id']) ? $params['product_id'] : md5($params['out_trade_no']),
            ]);
        }else{
            $queryParam = array_merge(except($this->payOption,'notify_url'), $queryParam);
        }

        $queryParam['sign'] = $this->makeSign($queryParam);

        return $queryParam;
    }

    /**
     * make wechat pay sign
     * @param $params
     * @return string
     */
    private function makeSign($params)
    {
        unset($params['sign']);
        //按字典序排序数组参数
        ksort($params);
        $str = '';
        foreach ($params as $key => $value) {
            if (!empty($value)) {
                strlen($str) and $str .= '&';
                $str .= "$key=$value";
            }
        }
        //生辰sign
        return strtoupper(md5($str . "&key=".$this->wechatKey));
    }

    /**
     * check sign
     * @param array $response
     * @return bool
     */
    private function checkSign(array $response)
    {
        if (! isset($response['sign'])) {
            return false;
        }

        $checkSignResult = $response['sign'] === $this->makeSign($response);
        if (! $checkSignResult) {
            $this->log->warning('验签失败,订单号：'.$response['out_trade_no']);
        }
        return $checkSignResult;
    }

    /**
     * set unified order status
     * @param $status
     * @param string $msg
     * @param array $data
     * @return $this
     */
    public function setUnifiedOrderStatus($status, $msg = '', $data = [])
    {
        $this->unifiedOrderStatus = $status;
        $this->unifiedOrderMsg = $msg;
        $this->successResponse = $data;
        return $this;
    }

    /**
     * unified order is success
     * @return mixed
     */
    public function isSuccess()
    {
        return $this->unifiedOrderStatus;
    }

    /**
     * get success response
     * @return mixed
     */
    public function getSuccessResponse()
    {
        return $this->successResponse;
    }

    /**
     * get error msg
     * @return mixed
     */
    public function getErrorMsg()
    {
        return $this->unifiedOrderResponseMsg;
    }

    /**
     * get order status
     * @param $out_trade_no
     * @return string
     */
    public function queryOrder($out_trade_no)
    {
        $result = (new Order())->query($this->makeOrderParam([
            'out_trade_no' => $out_trade_no
        ], self::ORDER_QUERY));

        if ($result["return_code"] != "SUCCESS") {
            $this->log->warning('查询订单失败,订单号：'.$out_trade_no);
            return 'QUERY_FAIL';
        }elseif (! $this->checkSign($result)) {
            return 'CHECK_SINE_ERROR';
        }

        return $result["trade_state"];
    }

    public function closeOrder($out_trade_no)
    {
        $result = (new Order())->close($this->makeOrderParam([
            'out_trade_no' => $out_trade_no
        ], self::ORDER_CANCEL));

        if ($result["return_code"] != "SUCCESS") {
            $this->log->warning('关闭订单失败,订单号：'.$out_trade_no);
            return false;
        }elseif (! $this->checkSign($result)) {
            return false;
        }

        $this->log->info('关闭订单,订单号：'.$out_trade_no);
        return $result["result_code"] == 'SUCCESS';
    }

    /**
     * the order is pay
     * @param $out_trade_no
     * @return bool
     */
    public function isPay($out_trade_no)
    {
        return $this->queryOrder($out_trade_no) == 'SUCCESS';
    }

    /**
     * the order is wait
     * @param $out_trade_no
     * @return bool
     */
    public function isWait($out_trade_no)
    {
        return $this->queryOrder($out_trade_no) == 'NOTPAY';
    }

    /**
     * the order is wait
     * @param $out_trade_no
     * @return bool
     */
    public function isClose($out_trade_no)
    {
        return $this->queryOrder($out_trade_no) == 'CLOSED';
    }
}