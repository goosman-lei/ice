<?php
namespace Ice\Filter;
class Filter {
    protected $config       = array();
    protected $strictMode   = FALSE;

    protected $defaultMap   = array();
    protected $defaultArr   = array();
    protected $defaultInt   = 0;
    protected $defaultFloat = 0.0;
    protected $defaultBool  = FALSE;
    protected $defaultStr   = '';

    protected $extendConfig;

    public function __construct($config, $strictMode = FALSE) {
        $this->config     = $config;
        $this->strictMode = $strictMode;
        if (isset($this->config['extend_path'])) {
            $this->extendConfig = new \F_Config($this->config['extend_path']);
        }
    }

    public function extend_filter(&$target, $data, $filter) {
        $extendSrcCode = $this->extendConfig->get($filter);
        if (empty($extendSrcCode)) {
            return ;
        }
        $proxy  = \F_Ice::$ins->workApp->proxy_filter->get($extendSrcCode, $this->strictMode);
        $target = $proxy->filter($data);
    }

    public function type_str(&$target, $data, $req = '__opt') {
        if (!isset($data)) {
            if ($req === '__req') return $this->reportMessage();
            else if ($req === '__opt') return TRUE;
            else return ($data = $req) || TRUE;
        }
        $target = (string)$data;
    }
    public function type_int(&$target, $data, $req = '__opt') {
        if (!isset($data)) {
            if ($req === '__req') return $this->reportMessage();
            else if ($req === '__opt') return TRUE;
            else return ($data = $req) || TRUE;
        }
        $target = (int)$data;
    }
    public function type_float(&$target, $data, $req = '__opt') {
        if (!isset($data)) {
            if ($req === '__req') return $this->reportMessage();
            else if ($req === '__opt') return TRUE;
            else return ($data = $req) || TRUE;
        }
        $target = (float)$data;
    }
    public function type_map(&$target, $data, $req = '__opt') {
        if (!isset($data)) {
            if ($req === '__req') return $this->reportMessage();
            else if ($req === '__opt') return TRUE;
            else return ($data = $req) || TRUE;
        }
        $target = new \U_Map();
    }
    public function type_arr(&$target, $data, $req = '__opt') {
        if (!isset($data)) {
            if ($req === '__req') return $this->reportMessage();
            else if ($req === '__opt') return TRUE;
            else return ($data = $req) || TRUE;
        }
        $target = array();
    }
    public function type_bool(&$target, $data, $req = '__opt') {
        if (!isset($data)) {
            if ($req === '__req') return $this->reportMessage();
            else if ($req === '__opt') return TRUE;
            else return ($data = $req) || TRUE;
        }
        $target = (bool)$data;
    }

    public function op_range(&$target, $data, $range) {
        if (!isset($data)) {
            return TRUE;
        }
        if (!is_numeric($data)) {
            return $this->reportMessage();
        }
        $rangeEles = explode(',', $range);
        foreach ($rangeEles as $ele) {
            $rangeNums = explode('_', $ele);
            $nNums = count($rangeNums);
            if ($nNums < 1 || $nNums > 2) {
                return $this->reportMessage();
            
            } else if ($nNums === 1) {
                if (!is_numeric($rangeNums[0])) {
                    return $this->reportMessage();
                } else if ($data == $rangeNums[0]) {
                    return TRUE;
                }
            } else if ($nNums === 2) {
                if (strlen($rangeNums[0]) === 0 && is_numeric($rangeNums[1]) && $data <= $rangeNums[1]) {
                    return TRUE;
                } else if (strlen($rangeNums[1]) === 0 && is_numeric($rangeNums[0]) && $data >= $rangeNums[0]) {
                    return TRUE;
                } else if (!is_numeric($rangeNums[0]) || !is_numeric($rangeNums[1])) {
                    return $this->reportMessage();
                } else if ($data >= $rangeNums[0] && $data <= $rangeNums[1]) {
                    return TRUE;
                }
            }
        }
        return $this->reportMessage();

    }

    public function op_match(&$target, $data, $pattern) {
        if (!isset($data)) {
            return TRUE;
        }
        switch ($pattern) {
            case 'email':
                $pattern = '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix';
                break;
            default:
                break;
        }
        if (!preg_match($pattern, $data)) {
            return $this->reportMessage();
        }
    }

    protected function reportMessage($message = '') {
        $bt = debug_backtrace();
        $op = substr(@$bt[1]['function'], 3);
        \F_Ice::$ins->mainApp->logger_comm->warn(array(
            'op' => $op,
        ), \F_ECode::FILTER_RUN_STRICT_UNEXPECT, 1);
        if ($this->strictMode) {
            throw new RunException($op, $message);
        } else {
            return FALSE;
        }
    }
}