<?php
/**
 * Created by PhpStorm.
 * User: shizhice
 * Date: 2017/8/16
 * Time: 下午3:40
 */

namespace Shizhice\Payment;


interface PayDriveInterface
{
    /**
     * PayDriveInterface constructor.
     * @param array $option
     */
    public function __construct(array $option, $logHandle);

    /**
     * 统一下单
     * @return mixed
     */
    public function unifiedOrder($item);

    /**
     * 当面付
     * @return mixed
     */
    public function native($item);

    /**
     * js api params
     * @return mixed
     */
    public function jsApiPay($item);

    /**
     * app pay params
     * @return mixed
     */
    public function appPay($item);

    /**
     * unified order is success
     * @return mixed
     */
    public function isSuccess();

    /**
     * get success response
     * @return mixed
     */
    public function getSuccessResponse();

    /**
     * get error msg
     * @return mixed
     */
    public function getErrorMsg();
}