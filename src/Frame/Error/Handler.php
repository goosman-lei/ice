<?php
namespace Ice\Frame\Error;
class Handler {
    public function __construct() {
        set_error_handler(array($this, '__errorHandler'));
    }

    public function __errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
        //PHP7版本错误兼容处理
        if (PHP_MAJOR_VERSION >= 7) {
            //重载方法，参数传入不全时，忽略错误
            if(strpos($errstr, 'Declaration of') === 0){
                return TRUE;
            }
        }
        switch ($errno) {
            case E_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                \F_Ice::$ins->mainApp->logger_comm->fatal(array(
                    'errno'      => $errno,
                    'errstr'     => $errstr,
                    'errfile'    => $errfile,
                    'errline'    => $errline,
                ), \F_ECode::PHP_ERROR);
                \F_Ice::$ins->runner->response->error(\F_ECode::PHP_ERROR);
                break;
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_WARNING:
            case E_USER_WARNING:
                \F_Ice::$ins->mainApp->logger_comm->warn(array(
                    'errno'      => $errno,
                    'errstr'     => $errstr,
                    'errfile'    => $errfile,
                    'errline'    => $errline,
                ), \F_ECode::PHP_WARN);
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            default:
                break;
        }
        return TRUE;
    }
}
