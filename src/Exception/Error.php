<?php

namespace Soen\Command\Exception;

use Soen\Command\Event\HandleExceptionEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
/**
 * Class Error
 * @package Soen\Command\Exception
 */
class Error
{
	/**
	 * @var int
	 */
	public $level = E_ALL;

	/**
	 * @var LoggerInterface
	 */
	public $logger;

	/**
	 * @var EventDispatcherInterface
	 */
	public $dispatcher;

	function __construct(int $level, LoggerInterface $logger)
    {
        $this->level  = $level;
        $this->logger = $logger;
        error_reporting(E_ALL);
        // 注册错误处理
        set_error_handler([$this, 'appError']);
        register_shutdown_function([$this, 'appShutdown']);
    }

	/**
	 * @param $ex
	 */
    public function appException($ex)
    {
        $event = new HandleExceptionEvent();
        $this->dispatch($event);
        // handle
        $this->handleException($ex);
    }

	/**
	 * Dispatch
	 * @param object $event
	 */
	protected function dispatch(object $event)
	{
		if (!isset($this->dispatcher)) {
			return;
		}
		$this->dispatcher->dispatch($event);
	}

	/**
	 * @param $errno
	 * @param $errstr
	 * @param string $errfile
	 * @param int $errline
	 */
    public function appError($errno, $errstr, $errfile = '', $errline = 0)
    {
        if (error_reporting() & $errno) {
            // 委托给异常处理
            if (static::isFatalWarning($errno, $errstr)) {
                $this->appException(new ErrorException($errno, $errstr, $errfile, $errline));
                return;
            }
            // 转换为异常抛出
            throw new ErrorException($errno, $errstr, $errfile, $errline);
        }
    }

    public function appShutdown()
    {
        //  获取程序终止后最后的错误
        $error = error_get_last();
        //  是否是致命错误
        $isFatal = in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
        if ($error && isset($error['type']) && self::isFatal($error['type'])) {
            // 委托给异常处理
            $this->appException(new ErrorException($error['type'], $error['message'], $error['file'], $error['line']));
        }
    }

	/**
	 * 异常处理
	 * @param \Throwable $ex
	 */
	public function handleException(\Throwable $ex)
	{
		// 日志处理
		if ($ex instanceof NotFoundException) {
			// 打印到屏幕
			println($ex->getMessage());
			return;
		}
		// 输出日志
		$this->log($ex);
	}

	/**
	 * 返回错误级别
	 * @param $errno
	 * @return string
	 */
	public static function levelType($errno)
	{
		if (static::isError($errno)) {
			return 'error';
		}
		if (static::isWarning($errno)) {
			return 'warning';
		}
		if (static::isNotice($errno)) {
			return 'notice';
		}
		return 'error';
	}

    /**
     * 是否为致命错误
     * @param $errno
     * @return bool
     */
    public static function isFatal($errno)
    {
        return in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }

    /**
     * 是否致命警告类型
     * 特殊的警告，出现后 try/catch 将无法捕获异常。
     * @param $errno
     * @param $errstr
     * @return bool
     */
    public static function isFatalWarning($errno, $errstr)
    {
        if ($errno == E_WARNING && strpos($errstr, 'require') === 0) {
            return true;
        }
        return false;
    }

	/**
	 * 是否警告类型
	 * 全部类型：http://php.net/manual/zh/errorfunc.constants.php
	 * @param $type
	 * @return bool
	 */
	public static function isWarning($errno)
	{
		return in_array($errno, [E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING]);
	}

	/**
	 * 是否通知类型
	 * 全部类型：http://php.net/manual/zh/errorfunc.constants.php
	 * @param $type
	 * @return bool
	 */
	public static function isNotice($errno)
	{
		return in_array($errno, [E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED, E_STRICT]);
	}

	/**
	 * 是否错误类型
	 * 全部类型：http://php.net/manual/zh/errorfunc.constants.php
	 * @param $type
	 * @return bool
	 */
	public static function isError($errno)
	{
		return in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR]);
	}

}