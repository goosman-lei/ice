<?php
namespace Ice\Frame;
class Logger {

    // 日志级别
    const LEVEL_FATAL  = 0x01;
    const LEVEL_WARN   = 0x02;
    const LEVEL_INFO   = 0x04;

    // 日志级别字面
    protected static $logLevelLiteral = array(
        self::LEVEL_FATAL  => 'fatal',
        self::LEVEL_WARN   => 'warn',
        self::LEVEL_INFO   => 'info',
    );

    // 用户设置数据
    protected $userDatas = array();

    protected $config;

    public function __construct($config) {
        $this->config = $config;
    }
    
    public function set($key, $value) {
        $keyEles = explode('.', $key);
        $refData = &$this->userDatas;
        $lastKey = array_pop($keyEles);
        foreach ($keyEles as $keyEle) {
            if (!isset($refData[$keyEle])) {
                $refData[$keyEle] = array();
            } else if (!is_array($refData[$keyEle])) {
                $refData[$keyEle] = (array)$refData[$keyEle];
            }
            $refData = &$refData[$keyEle];
        }

        $refData[$lastKey] = $value;
    }

    /**
     * add 
     * 
     * @param mixed $key 主key: 支持点分分段结构
     * @param mixed $subKey 子键. 不支持点分分段结构.
     *                      当$value为null时, $subKey的参数含义是要追加的值, 采用ascii数组追加
     * @param mixed $value 值
     * @access public
     * @return void
     */
    public function add($key, $subKey, $value = null) {
        $keyEles = explode('.', $key);
        $refData = &$this->userDatas;
        foreach ($keyEles as $keyEle) {
            if (!isset($refData[$keyEle])) {
                $refData[$keyEle] = array();
            } else if (!is_array($refData[$keyEle])) {
                $refData[$keyEle] = (array)$refData[$keyEle];
            }
            $refData = &$refData[$keyEle];
        }
        if (isset($value)) {
            $refData[$subKey] = $value;
        } else {
            $refData[] = $subKey;
        }
    }

    public function get($key) {
        $keyEles = explode('.', $key);
        $refData = &$this->userDatas;
        $lastKey = array_pop($keyEles);
        foreach ($keyEles as $keyEle) {
            if (!isset($refData[$keyEle]) || !is_array($refData[$keyEle])) {
                return null;
            }
            $refData = &$refData[$keyEle];
        }
        return isset($refData[$lastKey]) ? $refData[$lastKey] : null;
    }

    public function fatal($userLog, $errno = null, $depth = 0) {
        $this->set('errno', $errno);
        $this->log($userLog, self::LEVEL_FATAL, $depth + 1);
    }

    public function warn($userLog, $errno = null, $depth = 0) {
        $this->set('errno', $errno);
        $this->log($userLog, self::LEVEL_WARN, $depth + 1);
    }

    public function info($userLog, $depth = 0) {
        $this->log($userLog, self::LEVEL_INFO, $depth + 1);
    }

    protected function log($userLog, $level, $depth = 0) {
        $logString = $this->getLogStr($userLog, $level, $depth + 1);
        $logFile   = $this->getLogFile($level);
        $this->writeLog($logString, $logFile);
    }

    protected function getLogStr($userLog, $level, $depth = 0) {
        $logData = array();

        $runner = \F_Ice::$ins->runner;
        $logFmt = $level == self::LEVEL_INFO ? $this->config['log_fmt'] : $this->config['log_fmt_wf'];
        foreach ($logFmt as $field => $arg) {
            // 请求环境 及 请求相关信息
            if (strpos($field, 'client_env.') === 0) {
                $envName = substr($field, strlen('client_env.'));
                $logValue = $runner->clientEnv->$envName;
            } else if (strpos($field, 'server_env.') === 0) {
                $envName = substr($field, strlen('server_env.'));
                $logValue = $runner->serverEnv->$envName;
            } else if (strpos($field, 'request.') === 0) {
                $fieldName = substr($field, strlen('request.'));
                $logValue = $runner->request->$fieldName;
            } else {
                // 需要特殊处理的信息
                switch ($field) {
                    case 'fmt_time':
                        $fmt = empty($arg) ? 'Y-m-d H:i:s' : $arg;
                        $logValue = date($fmt, \F_Ice::$ins->runner->request->requestTime);
                        break;
                    case 'fmt_now':
                        $fmt = empty($arg) ? 'Y-m-d H:i:s' : $arg;
                        $logValue = date($fmt, time());
                        break;
                    case 'level':
                        $logValue = self::$logLevelLiteral[$level];
                        break;
                    case 'trace':
                        $logValue = self::getTraceInfo($depth + 1);
                        break;
                    case 'mem_used':
                        $logValue = round(memory_get_peak_usage() / 1048476) . 'M';
                        break;
                    default:
                        $logValue = $this->get($field);
                        break;
                }
            }

            $logData[$field] = empty($logValue) ? '-' : $logValue;
        }
        $logData['user_log'] = $userLog;

        return json_encode($logData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function getLogFile($level) {
        $logFile = $this->config['log_file'];
        $logPath = $this->config['log_path'];

        // 处理为绝对路径
        if (strpos($logFile, '/') !== 0) {
            $logFile = rtrim($logPath, '/') . '/' . $logFile;
        }

        if (isset($this->config['split'])) {
            $splitStr = ''; 
            //cli模式下需要实时切分日志, web模式下单次请求必须打到一个日志
            if(strtolower(php_sapi_name()) === 'cli'){
                $splitStr = date($this->config['split']['fmt']);
            } else {
                $splitStr = date($this->config['split']['fmt'], \F_Ice::$ins->runner->request->requestTime);
            }
            switch ($this->config['split']['type']) {
                case 'file':
                    $logFile .= $splitStr;
                    break;
                case 'dir':
                    $dirname = dirname($logFile);
                    $basename = basename($logFile);
                    $logFile = $dirname . '/' . $splitStr . '/' . $basename;
                    break;
                case 'none':
                default:
                    break;
            }
        }

        if ($level == self::LEVEL_WARN || $level == self::LEVEL_FATAL) {
            $logFile .= '.wf';
        }

        return $logFile;
    }

    protected function writeLog($logString, $logFile) {
        // 尝试检查并创建目录
        $dirname = dirname($logFile);
        if (!is_dir($dirname) && !@mkdir($dirname, 0755, TRUE)) {
            return FALSE;
        }

        return @file_put_contents($logFile, $logString . "\n", FILE_APPEND);
    }

    protected function getTraceInfo($depth = 0) {
        $bt = debug_backtrace();
        
        array_splice($bt, 0, $depth);

        $traceInfo = array();
        foreach ($bt as $index => $btInfo) {
            $file = isset($btInfo['file']) ? $btInfo['file'] : '-';
            $line = isset($btInfo['line']) ? $btInfo['line'] : '0';
            $class = isset($btInfo['class']) ? $btInfo['class'] . $btInfo['type'] : '';
            $func  = isset($btInfo['function']) ? $btInfo['function'] : '__unknown__';
            $args  = array();
            if (isset($btInfo['args'])) {
                foreach ($btInfo['args'] as $arg) {
                    if (is_array($arg)) {
                        $argStr = 'array(';
                        $argStr .= 'count=' . count($arg);
                        foreach ($arg as $k => $v) {
                            $argStr .= ', ' . mb_substr(json_encode($k, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 0, 32, 'UTF-8') . '=' . mb_substr(json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 0, 32, 'UTF-8');
                        }
                        $argStr .= ')';
                    } else if (is_object($arg)) {
                        $argStr = 'object(' . get_class($arg) . ')';
                    } else if (is_resource($arg)) {
                        $argStr = 'resource(' . get_resource_type($arg) . ')';
                    } else {
                        $argStr = json_encode($arg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    }
                    $args[] = $argStr;
                }
            }
            if ($index == 0) {
                $traceInfo[] = sprintf('%s:%d[Logger::%s()]', $file, $line, $func);
            } else {
                $traceInfo[] = sprintf('%s:%d[%s%s(%s)]', $file, $line, $class, $func, implode(',', $args));
            }
        }
        return $traceInfo;
    }
}
