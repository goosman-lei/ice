<?php
namespace Ice\Util;
class DArray {

    /**
     * Determine whether the given value is array accessible.
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|int  $key
     * @return bool
     */
    public static function exists($array, $key)
    {
        if (is_array($array)) {
            return array_key_exists($key, $array);
        }

        return $array->offsetExists($key);
    }

    /**
     * fromCommaExp
     * 从逗号表达式转换数组
     * @param string $commaExp
     * @param bool $isTrim 是否对元素trim
     * @static
     * @access public
     * @return void
     */
    public static function fromCommaExp($commaExp, $isTrim = TRUE) {
        if (is_string($commaExp)) {
            $commaExp = explode(',', preg_replace(';\s*,\s*;', ',', $commaExp));
        } else if (!is_array($commaExp)) {
            $commaExp = (array)$commaExp;
        }
        if (!$isTrim) {
            return $commaExp;
        }
        foreach ($commaExp as &$ele) {
            $ele = $ele;
        }
        return $commaExp;
    }

    /**
     * iterateValueCallback
     * 迭代处理值的回调
     * @param mixed $val
     * @param mixed $key
     * @param mixed $hint
     * @static
     * @access protected
     * @return void
     */
    protected static function iterateValueCallback(&$val, $key, $hint) {
        switch ($hint) {
            case 'int':
                $val = intval($val);
                break;
            case 'float':
                $val = floatval($val);
                break;
            case 'bool':
                $val = (bool)$val;
                break;
            case 'array':
                $val = (array)$val;
                break;
            case 'object':
                $val = (object)$val;
                break;
            case 'string':
                $val = strval($val);
                break;
            case 'trim':
                $val = trim($val);
                break;
            case 'json':
                $val = json_encode($val);
                break;
            case 'strtolower':
                $val = strtolower($val);
                break;
            case 'extractvalues':
                $val = array_values($val);
                break;
            case 'strvalues':
                $val = implode(',',array_values($val));
                break;
            case 'lz4_compress':
                $val = lz4_compress($val);
                break;
            default:
                break;
        }
    }

    /**
     * iterateValue
     * 迭代处理数组的值, 主要用于类型转换等操作
     * @param array $array
     * @param enum $type int float bool array object string trim json extractvalues strvalues
     * @static
     * @access public
     * @return void
     */
    public static function iterateValue($array, $type) {
        $type = strtolower($type);
        if (!is_array($array) || !in_array($type, array( 'int', 'float', 'bool', 'array',
            'object', 'string', 'trim', 'json', 'strtolower', 'extractvalues','strvalues',
            'lz4_compress'))) {
            return FALSE;
        }
        array_walk($array, array(__CLASS__, 'iterateValueCallback'), $type);
        return $array;
    }

    const PICKUP_KK_AUTOINCR  = NULL;
    const PICKUP_KK_HOLD      = TRUE;
    const PICKUP_VK_ENTIRE    = NULL;
    const PICKUP_VD_SKIP      = FALSE;
    const PICKUP_VD_OVERWRITE = TRUE;
    const PICKUP_VD_MERGE     = NULL;

    /**
     * pickup
     * 从二维数组中提取信息
     * @param array $array 目标二维数组
     * @param mixed $valueKey
     *      \Ice\CommLib\Util\Array::PICKUP_VK_ENTIRE: 使用整个子数组作为提取后的值
     *      array('field1', 'field2', ...): 使用子数组中指定的几列组成新数组作为提取后的值
     *      string: 使用指定子数组中一列值作为提取后的值
     * @param mixed $keyKey
     *      \Ice\CommLib\Util\Array::PICKUP_KK_HOLD: 保留原来的Key
     *      \Ice\CommLib\Util\Array::PICKUP_KK_AUTOINCR: 使用自增长Key
     *      string: 使用子数组中一列值作为提取后的key
     * @param mixed $onDup
     *      \Ice\CommLib\Util\Array::PICKUP_VD_MERGE: 合并重复key对应的值
     *      \Ice\CommLib\Util\Array::PICKUP_VD_OVERWRITE: 重复key的值, 后面覆盖前面
     *      \Ice\CommLib\Util\Array::PICKUP_VD_SKIP: 重复key的值, 保留前面, 跳过后面
     * @static
     * @access public
     * @return array 提取到的信息
     */
    public static function pickup($array, $valueKey, $keyKey = self::PICKUP_KK_HOLD, $onDup = self::PICKUP_VD_SKIP) {
        $target = array();
        if (is_array($array)) {
            $index = -1;
            foreach ($array as $k => $v) {
                $key = $keyKey === self::PICKUP_KK_AUTOINCR
                    ? (++ $index)
                    : ($keyKey === self::PICKUP_KK_HOLD ? $k : @$v[$keyKey]);

                if (is_string($valueKey)) {
                    $value = @$v[$valueKey];
                } else if (is_array($valueKey)) {
                    $value = array();
                    foreach ($valueKey as $vk) {
                        $value[$vk] = @$v[$vk];
                    }
                } else {
                    $value = $v;
                }

                if ($onDup === self::PICKUP_VD_MERGE) {
                    $target[$key][] = $value;
                } else if ($onDup === self::PICKUP_VD_OVERWRITE) {
                    $target[$key] = $value;
                } else if (!array_key_exists($key, $target)) {
                    $target[$key] = $value;
                }
            }
        }
        return $target;
    }

    /**
     * pickupFields
     * 提取数组中指定字段
     * @param mixed $array
     * @param mixed $valueKey
     *      'field1, field2, field3'
     *      'field'
     *      array('field1', 'field2', ...)
     * @static
     * @access public
     * @return void
     */
    public static function pickupFields($array, $valueKey, $onlyKeyExist = false) {
        $valueKeys = self::fromCommaExp($valueKey);
        $retArr = array();
        foreach ($valueKeys as $valueKey) {
            if ($onlyKeyExist === true && !array_key_exists($valueKey, $array)) {
                continue;
            }
            $retArr[$valueKey] = @$array[$valueKey];
        }
        return $retArr;
    }

    /**
     * isAllInclude
     * 检查一组元素全部都在另一组元素中出现
     * @param mixed $needle 期望被包含的元素组. 可以是单个元素, 数组, 或逗号表达式
     * @param mixed $haystack 用于检测包含的元素组. 可以是单个元素, 数组, 或逗号表达式
     * @param mixed $strict
     * @static
     * @access public
     * @return void
     */
    public static function isAllInclude($needle, $haystack, $strict = FALSE) {
        $needle   = self::fromCommaExp($needle);
        $haystack = self::fromCommaExp($haystack);
        foreach ($needle as $ele) {
            if (!in_array($ele, $haystack, $strict)) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * isAllKeyInclude
     * 检查一组元素的key
     * @param mixed $requiredKeys 期望的key列表. 可以是单个元素, 数组, 或逗号表达式
     * @param mixed $haystack  用于检测包含的数组
     * @param mixed $strict
     * @static
     * @access public
     * @return void
     */
    public static function isAllKeyInclude($requiredKeys, $haystack, $strict = FALSE) {
        if (!is_array($haystack)) {
            return FALSE;
        }
        $haystackKeys = array_keys($haystack);
        return self::isAllInclude($requiredKeys, $haystackKeys, $strict);
    }

    private static $__reverse;
    private static $__valueKey;

    /**
     * sortWithValue
     * 对二维数组, 使用子数组的某列值进行排序
     * @param array $array
     * @param string $valueKey
     * @param string $type string time numeric
     * @param bool $reverse
     * @static
     * @access public
     * @return void
     */
    public static function sortWithValue(&$array, $valueKey, $type = 'string', $reverse = FALSE) {
        self::$__valueKey = $valueKey;
        self::$__reverse  = (bool)$reverse;
        $type = strtolower($type);
        switch ($type) {
            case 'time':
                uasort($array, array(__CLASS__, '__cmpTime'));
                break;
            case 'numeric':
                uasort($array, array(__CLASS__, '__cmpNumeric'));
                break;
            case 'string':
            default:
                uasort($array, array(__CLASS__, '__cmpString'));
                break;
        }
    }

    /**
     * __cmpString
     * 内部使用的字符串比较
     * @param mixed $a
     * @param mixed $b
     * @static
     * @access protected
     * @return void
     */
    protected static function __cmpString($a, $b) {
        $realA = self::$__reverse ? array_get($b, self::$__valueKey) : array_get($a, self::$__valueKey);
        $realB = self::$__reverse ? array_get($a, self::$__valueKey) : array_get($b, self::$__valueKey);
        return strcasecmp($realA, $realB);
    }

    /**
     * __cmpNumeric
     * 内部使用的数值比较
     * @param mixed $a
     * @param mixed $b
     * @static
     * @access protected
     * @return void
     */
    protected static function __cmpNumeric($a, $b) {
        $realA = self::$__reverse ? array_get($b, self::$__valueKey) : array_get($a, self::$__valueKey);
        $realB = self::$__reverse ? array_get($a, self::$__valueKey) : array_get($b, self::$__valueKey);
        return $realA - $realB;
    }

    /**
     * __cmpTime
     * 内部使用的时间比较
     * @param mixed $a
     * @param mixed $b
     * @static
     * @access protected
     * @return void
     */
    protected static function __cmpTime($a, $b) {
        $realA = self::$__reverse ? array_get($b, self::$__valueKey) : array_get($a, self::$__valueKey);
        $realB = self::$__reverse ? array_get($a, self::$__valueKey) : array_get($b, self::$__valueKey);
        return strtotime($realA) - strtotime($realB);
    }

    /**
     * icaseKeySearch
     * 忽略大小写的Key查找
     * @param string $needle
     * @param array $haystack
     * @static
     * @access public
     * @return mixed
     */
    public static function icaseKeySearch($needle, $haystack) {
        $haystack = (array)$haystack;
        foreach ($haystack as $k => $v) {
            if (strcasecmp($k, $needle) === 0) {
                return $v;
            }
        }
        return NULL;
    }

    /**
     * icaseValueSearch
     * 忽略大小写的value查找
     * @param string $needle
     * @param array $haystack
     * @static
     * @access public
     * @return mixed
     */
    public static function icaseValueSearch($needle, $haystack) {
        $haystack = (array)$haystack;
        foreach ($haystack as $k => $v) {
            if (strcasecmp($v, $needle) === 0) {
                return $k;
            }
        }
        return NULL;
    }

    /**
     * multiArraySort
     * 根据指定的key对二维数组排序
     * @param mixed $multi_array
     * @param mixed $sort_key
     * @param mixed $sort
     * @static
     * @access public
     * @return void
     */
    public static function multiArraySort($multiArray, $sortKey, $sort = SORT_ASC, $sortType = SORT_REGULAR) {
        if (is_array($multiArray)) {
            $keyArray = self::pickup($multiArray, $sortKey);
        } else {
            return array();
        }
        array_multisort($keyArray, $sort, $sortType, $multiArray);
        return $multiArray;
    }

    /**
     * sliceWithSortedNum
     * 数值数组, 排序, 并按照给定值分段获取(用于id列表类的分页)
     * @param array $array
     * @param string $op biggest, smallest
     * @param mixed $value FALSE表示取TOP
     * @param int $limit
     * @static
     * @access public
     * @return void
     */
    public static function sliceWithSortedNum($array, $op, $value, $limit) {
        $array = (array)$array;
        asort($array, SORT_NUMERIC);
        $array = array_values($array);

        $length = count($array);
        if ($value === FALSE) {
            $index = $length;
        } else if (FALSE === ($index = array_search($value, $array))) {
            return array();
        }

        switch ($op) {
            case 'biggest':
                $offset = max(0, $index - $limit);
                return array_reverse(array_slice($array, $offset, $index - $offset));
                break;
            case 'smallest':
                return array_slice($array, $index % $length + intval($index < $length), $limit);
                break;
            default:
                throw new Exception('unknown op[' . $op . '] for ' . __METHOD__);
                break;
        }
    }

    /**
     * unsetFields
     * 将传入数组中的指定key unset掉
     * @param array $array
     * @param mixed $keys 单个key, 数组, 逗号表达式
     * @static
     * @access public
     * @return void
     */
    public static function unsetFields($array, $keys) {
        $array = (array)$array;
        $keys = self::fromCommaExp($keys);
        foreach ($array as &$val) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $val)) {
                    unset($val[$key]);
                }
            }
        }
        return $array;
    }

    /**
     * sortWithColumn
     * 按照指定列排序
     * @param mixed $array
     * @param mixed $sortVals
     * @param mixed $keyName 当keyName为null时, 是以数据的key排序
     * @param mixed $preserveKey
     * @static
     * @access public
     * @return void
     */
    public static function sortWithColumn($array, $sortVals, $keyName = null, $preserveKey = TRUE) {
        if (is_null($keyName)) {
            $tmpArr   = $array;
        } else {
            $tmpArr   = self::pickup($array, self::PICKUP_VK_ENTIRE, $keyName); // 排序列为key的数组
        }
        $sortKeys     = array_keys($tmpArr); // 排序列的key列表
        $sortKeysFlip = array_flip($sortKeys); // 排序列key对应下标
        $arrKeys = array_keys($array); // 原始数组的key列表

        $resultArr = array();
        $key = -1;
        foreach ($sortVals as $sortVal) {
            if ($preserveKey) {
                $index = $sortKeysFlip[$sortVal];
                $key   = $arrKeys[$index];
            } else {
                @$key ++;
            }
            $value = $tmpArr[$sortVal];
            $resultArr[$key] = $value;
        }
        return $resultArr;
    }
    /**
     * 二维数组按照某个key与一维数组去重
     *@param arraySecond : 需要进行去重的二维数组
     *@param uniqKey     : 需要进行去重的二维数组的key
     *@param array       : 一维数组
     *@return arraySecond: 返回原二维数组
     */
    public static function arrayUnique($arraySecond,$uniqKey,$array) {
        $arrayResult = array();
        if(empty($arraySecond) || empty($uniqKey) || empty($array)) {
            return array();
        }
        for($i=0; $i<count($arraySecond); $i++) {
            if(!in_array($arraySecond[$i][$uniqKey],$array,TRUE)) {
                $arrayResult[] = $arraySecond[$i];
            }
        }
        return $arrayResult;
    }

    /**
     * 将二维数组数据按照key修改成一维数组
     * @param $array : 输入的二维数组
     * @param $key   : 输入的key
     * return $new_array : 变幻之后的数组
     * */
    public function secondArrayToOne($array, $key) {
        if(empty($array) || empty($key)) {
            return array();
        }
	    $new_arr = array();
        for($i=0; $i<count($array); $i++) {
            $new_arr[] = $array[$i][$key];
        }
        return $new_arr;
    }

    /**
     * sameSortArrays 
     * 使第二个数组与第一个数组顺序相同
     * @access public
     * @return void
     */
    public function sameSortArrays($array1, $array2) {
        $return = array();
        foreach ($array1 as $key => $item) {
            $return[$key] = $array2[$key];
        }
        return $return;
    }

    /*
     *combineSameKey : 两个数组按照key合并，不同key的保留
     *input : $arrayFirst = array(  "yan" => array('jaaj'),  "ajdljasf" => array('aljdljaslfjalfjal') );
     *input : $arraySecond = array( "yan" => array('jaaj2') ,'aaa' => 'aaa');
     *output: $res = array(  "yan" => array('jaaj','jaaj2'),  "ajdljasf" => array('aljdljaslfjalfjal'), 'aaa' => 'aaa');
     */
    public function combineSameKey($arrayFirst, $arraySecond) {
        if(empty($arrayFirst) && empty($arraySecond)) {
            return array();
        }
        if(empty($arrayFirst)) {
            return $arraySecond;
        }
        if(empty($arraySecond)) {
            return $arrayFirst;
        }
        //key 合并去重
        $keys = array_unique(array_merge(array_keys($arrayFirst),array_keys($arraySecond)));
        //数组下标重新排列
        $keys = array_merge(array_filter($keys));
        $res = array();
        for($i=0; $i<count($keys); $i++) {
            $key = $keys[$i];
            if(empty($arrayFirst[$key]) && !empty($arraySecond[$key])) {
                $res[$key] = $arraySecond[$key];
            } else if(!empty($arrayFirst[$key]) && empty($arraySecond[$key])) {
                $res[$key] = $arrayFirst[$key];
            } else {
                $res[$key] = array_merge($arrayFirst[$key],$arraySecond[$key]);
            }
        }
        return $res;
    }

    /**
     * where 
     * 用闭包函数过滤数组
     * @param mixed $array 
     * @param callable $callback 
     * @static
     * @access public
     * @return void
     */
    public static function where($array, callable $callback)
    {
        $filtered = [];
        if (!is_array($array)) {
            return $filtered;
        }

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;

    }

    public static function get($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if ((! is_array($array) || ! array_key_exists($segment, $array)) &&
                    (! $array instanceof ArrayAccess || ! $array->offsetExists($segment))) {
                return value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Pluck an array of values from an array.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|array  $value
     * @param  string|array|null  $key
     * @return array
     */
    public static function pluck($array, $value, $key = null)
    {
        $results = [];

        $value = is_string($value) ? array($value) : $value;

        list($value, $key) = static::explodePluckParameters($value, $key);

        if(is_array($array) && $array){
            foreach ($array as $item) {
                $itemValue = array();

                if (empty($value)) {
                    $itemValue = $item;
                } else {
                    foreach ($value as $itemKey2 => $itemValue2) {
                        $itemValue[$itemKey2] = data_get($item, $itemValue2);
                    }
                }


                // If the key is "null", we will just append the value to the array and keep
                // looping. Otherwise we will key the array using the value of the key we
                // received from the developer. Then we'll return the final array form.
                if (is_null($key)) {
                    $results[] = $itemValue;
                } else {
                    $itemKey = data_get($item, $key);
                    if (!array_key_exists($itemKey, $results)) {
                        $results[$itemKey] = $itemValue;
                    }
                }
            }
        }

        return $results;
    }

    protected static function explodePluckParameters($value, $key)
    {
        $returnValue = array();
        
        if(is_array($value) && $value){
            foreach ($value as $item) {
                $returnKey = is_string($item) ? $item : implode('.', $item);
                $returnValue[$returnKey] = is_string($item) ? explode('.', $item) : $item;
            }
        }

        $key = is_null($key) || is_array($key) ? $key : explode('.', $key);

        return [$returnValue, $key];
    }
}
