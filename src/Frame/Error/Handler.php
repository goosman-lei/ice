<?php
namespace Ice\Frame\Error;
class Handler {
    public function __construct() {
        set_error_handler(array($this, '__errorHandler'));
    }

    public function __errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
        switch ($errno) {
            case E_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                \F_Ice::$ins->mainApp->logger_common->fatal(array(
                    'errno'      => $errno,
                    'errstr'     => $errstr,
                    'errfile'    => $errfile,
                    'errline'    => $errline,
                    'errcontext' => $errcontext,
                ), \F_ECode::PHP_ERROR);
                \F_Ice::$ins->runner->response->error(\F_ECode::PHP_ERROR);
                break;
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_WARNING:
            case E_USER_WARNING:
                \F_Ice::$ins->mainApp->logger_common->warn(array(
                    'errno'      => $errno,
                    'errstr'     => $errstr,
                    'errfile'    => $errfile,
                    'errline'    => $errline,
                    'errcontext' => $errcontext,
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
