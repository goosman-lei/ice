<?php
namespace Ice\Util;
/**
 * 多语言工具 
 * 用法示例代码:
$i18n = new I18N('en', array('en', 'cn'), 'cn'); // 创建一个多语言工具实例. 其当前语言为en, 合法语言包括en/cn, 当前语言(en)找不到字面时, 使用默认语言cn
$ret  = $i18n->addCfg('i18n.ini'); // 添加多语言配置文件. 标准ini文件, 用分节作为语言名
$i18n->literal('nice.club.default.zaned_wording', array('username' => 'goosman.lei'), 'cn'); // 获取指定Key的字面, 其中有替换变量username. 注意这里强制获取cn语言的.
$i18n->literalAll('nice.club.default.zaned_wording', array('username' => 'goosman.lei')); // 获取指定Key的所有已配置可用语言字面.
 */
class I18N {

    protected $i18nMapping = array();

    protected $currLang    = 'cn'; // 当前语言
    protected $defaultLang = 'cn'; // 指定语言没有时的默认语言
    protected $validLang = array('cn', 'en'); // 合法语言

    public function __construct($currLang, $validLang = array('cn', 'en'), $defaultLang = 'cn') {
        $this->setLang($currLang, $validLang, $defaultLang);
    }

    /**
     * setLang 
     * 修改语言环境
     * @param mixed $currLang 
     * @param string $defaultLang 
     * @param string $validLang 
     * @param 'en') $'en') 
     * @access public
     * @return void
     */
    public function setLang($currLang, $validLang = array('cn', 'en'), $defaultLang = 'cn') {
        $this->currLang    = $currLang;
        $this->defaultLang = $defaultLang;
        $this->validLang   = $validLang;
    }

    /**
     * addCfg 
     * 添加多语言配置文件
     * @param mixed $cfgFile 
     * @param string $namespace 
     * @access public
     * @return void
     */
    public function addCfg($cfgFile, $namespace = '__default') {
        if (!is_file($cfgFile)) {
            return FALSE;
        }

        $allInfos = parse_ini_file($cfgFile, TRUE);
        return $this->addConf($allInfos, $namespace);
    }

    /**
     * addConf 
     * @desc 从字符串读取多语言配置
     * @param mixed $conf 
     * @param string $namespace 
     * @return void
     */
    public function addConf($conf, $namespace = '__default') {
        $cfgInfo = $this->loadCfg($conf);
        if (isset($this->i18nMapping[$namespace])) {
            $this->i18nMapping[$namespace] = array_merge($this->i18nMapping[$namespace], $cfgInfo);
        } else {
            $this->i18nMapping[$namespace] = $cfgInfo;
        }
        return TRUE;
    }

    /**
     * literal 
     * 获取单key单语言字面
     * @param mixed $key 
     * @param array $argv 
     * @param mixed $usedLang 
     * @param string $namespace 
     * @access public
     * @return void
     */
    public function literal($key, $argv = array(), $usedLang= NULL, $namespace = '__default') {
        $usedI18nMapping = $this->i18nMapping[$namespace];
        // step 1.1: 确定调用方需求使用的语言
        $lang = $this->currLang;
        if (!is_null($usedLang)) {
            $lang = $usedLang;
        }

        // step 1.2: 增加备选语言
        $alternateLangs = array($lang);
        if ($lang != $this->defaultLang) {
            $alternateLangs[] = $this->defaultLang;
        }

        // step 2: 从语言库取信息
        $literal = FALSE;
        $keyEles = explode('.', $key);
        foreach ($alternateLangs as $trialLang) {
            if (!isset($usedI18nMapping[$trialLang])) {
                continue;
            }
            if (!in_array($trialLang, $this->validLang)) {
                continue;
            }
            $info    = $usedI18nMapping[$trialLang];
            $literal = $this->searchLiteral($info, $keyEles, '{', '}');
            if (empty($literal)) { // 没有找到字面值, 尝试下一个备选语言
                continue;
            }
            // 找到字面值, 退出查找过程
            break;
        }

        // step 3: 返回结果处理
        if ($literal === FALSE) {
            return '';
        } else if (is_string($literal)) {
            return DString::placeholderReplace($literal, $argv);
        } else {
            return $literal;
        }
    }

    /**
     * literalAll 
     * 获取全语言字面
     * @param mixed $key 
     * @param array $argv 
     * @param string $namespace 
     * @access public
     * @return void
     */
    public function literalAll($key, $argv = array(), $namespace = '__default') {
        $usedI18nMapping = $this->i18nMapping[$namespace];
        $allInfos = array();
        foreach ($usedI18nMapping as $lang => $info) {
            $allInfos[$lang] = $this->literal($key, $argv, $lang, $namespace);
        }
        return $allInfos;
    }

    protected function loadCfg($allInfos) {
        if (!is_array($allInfos)) {
            $allInfos = array();
        }
        $rInfos = array();
        foreach ($allInfos as $k => $infos) {
            $tmpRInfos = array();
            foreach ($infos as $ik => $val){
                $rKeys = explode('.', $ik);
                $tmp = &$tmpRInfos;
                foreach ($rKeys as $rKey){
                    $tmp = &$tmp[$rKey];
                }
                $tmp = $val;
            }
            $rInfos[$k] = $tmpRInfos;
        }
        return $rInfos;
    }

    protected function searchLiteral($info, $keys) {
        foreach ($keys as $k) {
            if (!is_array($info) || !array_key_exists($k, $info)) {
                return '';
            }
            $info = $info[$k];
        }
        return $info;
    }
}
