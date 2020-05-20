<?php


namespace Soen\Console\Exception;

use Soen\Command\Event\HandleExceptionEvent;

/**
 * Class Error
 * @package Soen\Command\Exception
 */
class Error
{
    function __construct()
    {
        error_reporting(E_ALL);
        // 注册错误处理
        set_error_handler([$this, 'appError']);
        register_shutdown_function([$this, 'appShutdown']);
    }
    
    public function appException($ex)
    {
        $event = new HandleExceptionEvent();
        $this->dispatch($event);
        // handle
        $this->handleException($ex);
    }
    
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

}