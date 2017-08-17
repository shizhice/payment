<?php
namespace Shizhice\Payment;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Shizhice\Payment\Drives\Alipay;
use Shizhice\Payment\Drives\Wechat;

class Payment
{
    static private $_instance;
    protected $defaultDrive = 'wechat';

    protected $useDrive;

    protected $drives = [
        'wechat' => Wechat::class,
        'alipay' => Alipay::class,
    ];

    protected $logPath = __DIR__.'/logs';

    /**
     * Payment constructor.
     * @param string|null $drive
     */
    public function __construct(string $drive = null)
    {
        $drive and $this->useDrive = $drive;
        self::$_instance = $this;
    }

    /**
     * 变更payment drive
     * @param string|null $drive
     * @return Payment
     */
    static public function drive(string $drive = null)
    {
        $drive and self::getInstance()->useDrive = $drive;

        return self::getInstance();
    }

    /**
     * 初始化payment drive
     * @param array $config
     * @return mixed
     * @throws \Exception
     */
    static public function init(array $config, $logPath = __DIR__.'/logs', $schema = 'daily')
    {
        $drive = strtolower(! is_null(self::getInstance()->useDrive)
                        ? self::getInstance()->useDrive
                        : self::getInstance()->defaultDrive);

        if (! isset(self::getInstance()->drives[$drive])) {
            throw new \Exception("[$drive] drive not found");
        }

        $class = self::getInstance()->drives[$drive];

        $logHandle = new Logger(strtoupper($drive.'_ORDER_LOG'));
        $logHandle->pushHandler(new StreamHandler(self::getLogFilePath($logPath, $schema)));

        $payInstance = new $class($config, $logHandle);
        if (! $payInstance instanceof PayDriveInterface) {
            throw new \Exception("The [$drive] dirve must implements ".PayDriveInterface::class);
        }

        return $payInstance;
    }

    static private function getLogFilePath($logPath, $schema)
    {
        if(! is_dir($logPath)) {
            throw new \Exception("$logPath is not a dir");
        }

        if (! in_array($schema, ['daily','single'])) {
            throw new \Exception("log schema only support daily and single");
        }

        return $logPath.'/'.($schema == 'single' ? 'order.log' : 'order_'.date('Y_m_d').'.log');
    }
    /**
     * 获取payment实例
     * @return Payment
     */
    static private function getInstance()
    {
        is_null(self::$_instance) and self::$_instance = new self;
        return self::$_instance;
    }
}