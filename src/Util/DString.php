<?php
namespace Ice\Util;
class DString {
    /**
     * omitTail
     * 当字符串超过指定长度时, 截断末尾多余部分, 并发补充后缀串, 保证长度不变.
     * @param string $string
     * @param int $maxLen
     * @param string $suffix
     * @param string $charset
     * @static
     * @access public
     * @return void
     */
    public static function omitTail($string, $maxLen, $suffix = '...', $charset = 'utf-8') {
        $string    = strval($string);
        $suffix    = strval($suffix);
        $suffixLen = mb_strlen($suffix, $charset);
        $maxLen    = max(1, intval($maxLen));
        // 小于最大长度, 不需要截断
        if (mb_strlen($string, $charset) <= $maxLen) {
            return $string;
        }
        // 需要截断, 但最大长度小于后缀串长度, 取后缀串首部
        if ($maxLen <= $suffixLen) {
            return mb_substr($suffix, 0, $maxLen, $charset);
        }
        // 截断并添加后缀串
        return mb_substr($string, 0, $maxLen - $suffixLen, $charset) . $suffix;
    }

    /**
     * dbcToSbc
     * 半角转全角
     * @param string $s
     * @param string $charset utf-8 or gbk
     * @static
     * @access public
     * @return string
     */
    public static function dbcToSbc($s, $charset = 'utf-8') {
        $ts = '';
        $lowerCharset = str_replace('-', '', strtolower($charset));
        switch ($lowerCharset) {
            case 'utf8':
                $i  = -1;
                $cs = array_values(unpack('C*', $s));
                $l  = count($cs);
                while (++ $i < $l) {
                    $c = $cs[$i];
                    if ($c >= 0x21 && $c <= 0x5f) {
                        $ts .= pack('CCC', 0xef, 0xbc, 0x60 + $c);
                    } else if ($c >= 0x60 && $c <= 0x7e) {
                        $ts .= pack('CCC', 0xef, 0xbd, 0x20 + $c);
                    } else {
                        $ts .= $s[$i];
                    }
                }
                break;
            case 'gbk':
                $i  = -1;
                $cs = array_values(unpack('C*', $s));
                $l  = count($cs);
                while (++ $i < $l) {
                    $c = $cs[$i];
                    // 双字节字跳过下一字节(GBK第二字节和ascii码有重叠)
                    if ($c >= 0x81) {
                        $ts .= $s[$i] . $s[$i + 1];
                        $i ++;
                    } else if ($c >= 0x21 && $c <= 0x7e) {
                        $ts .= pack('CC', 0xa3, 0x80 + $c);
                    } else {
                        $ts .= $s[$i];
                    }
                }
                break;
            default:
                return FALSE;
                break;
        }
        return $ts;
    }

    /**
     * sbcToDbc
     * 全角转半角
     * @param string $s
     * @param string $charset utf-8 or gbk
     * @static
     * @access public
     * @return void
     */
    public static function sbcToDbc($s, $charset = 'utf-8') {
        $ts = '';
        $lowerCharset = str_replace('-', '', strtolower($charset));
        switch ($lowerCharset) {
            case 'utf8':
                $cs = array_values(unpack('C*', $s));
                $l  = count($cs) - 2;
                // 不足3字节跳出
                if ($l <= 0) {
                    $ts = $s;
                    break;
                }
                $i  = -1;
                while (++ $i < $l) {
                    $c1 = $cs[$i];
                    $c2 = $cs[$i + 1];
                    $c3 = $cs[$i + 2];
                    // 全角符号转换并跳过当前字, 其他字符跳过当前字节
                    if ($c1 === 0xef && $c2 === 0xbc && $c3 >= 0x81 && $c3 <= 0xbf) {
                        $ts .= chr($c3 - 0x60);
                        $i += 2;
                    } else if ($c1 === 0xef && $c2 === 0xbd && $c3 >= 0x80 && $c3 <= 0x9e) {
                        $ts .= chr($c3 - 0x20);
                        $i += 2;
                    } else {
                        $ts .= $s[$i];
                    }
                }
                $ts .= substr($s, $i);
                break;
            case 'gbk':
                $cs = array_values(unpack('C*', $s));
                $l  = count($cs) - 1;
                // 不足2字节跳出
                if ($l <= 0) {
                    $ts = $s;
                    break;
                }
                $i  = -1;
                $c2 = $cs[0];
                while (++ $i < $l) {
                    $c1 = $cs[$i];
                    $c2 = $cs[$i + 1];
                    // 全角符号转换并跳过当前字, 其他字符跳过当前字节
                    if ($c1 === 0xa3 && $c2 >= 0xa1 && $c2 <= 0xfe) {
                        $ts .= chr($c2 - 0x80);
                        $i ++;
                    } else {
                        $ts .= $s[$i];
                    }
                }
                $ts .= substr($s, $i);
                break;
            default:
                return FALSE;
                break;
        }
        return $ts;
    }

    public static function versionDiff($v1, $v2) {
        $i1 = explode('.', $v1);
        $i2 = explode('.', $v2);
        $l1 = count($i1);
        $l2 = count($i2);

        $len = max($l1, $l2);
        for ($i = 0; $i < $len; $i++) {
            $p1 = isset($i1[$i]) ? intval($i1[$i]) : 0;
            $p2 = isset($i2[$i]) ? intval($i2[$i]) : 0;

            if ($p1 > $p2) {
                return 1;
            } elseif ($p1 < $p2) {
                return -1;
            }
        }
        return 0;
    }

    /**
     * filterMobile
     * 过滤手机号
     * @param string $mobile
     * @access static public
     * @return string
     **/
    public static function filterMobile($mobile) {
        $mobile = trim(strtr($mobile, array('-' => '', '(' => '', ')' => '', ' ' => '', "\xc2\xa0" => '','+'=>'','*'=>'')));
        //提取数字
        $mobile = preg_replace('/[^\d]/ ','',$mobile);
        if (empty($mobile)) {
            $mobile = '';
        } elseif (substr($mobile, 0, 3) === '+86') {
            $mobile = substr($mobile, 3);
        } elseif (substr($mobile, 0, 4) === '0086') {
            $mobile = substr($mobile, 4);
        }
        return $mobile;
    }

    /**
     * randStr
     * 生成指定长度的字符串
     * @param int $length
     * @static
     * @access public
     * @return void
     */
    public static function randStr($length) {
        static $mapping = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
        $randStr = '';
        while ($length -- > 0) {
            $randStr .= $mapping[mt_rand(0, 62)];
        }
        return $randStr;
    }

    /**
     * placeholderReplace
     * 占位符类的字符串替换
     * @param mixed $target "{uname} like your photo in {clubName}"
     * @param mixed $placeHolders array('uname' => '张三', 'clubName' => '张三的小圈')
     * @param string $leftDelimiter '{'
     * @param string $rightDelimiter '}'
     * @static
     * @access public
     * @return void
     */
    public static function placeholderReplace($target, $placeHolders, $leftDelimiter = '{', $rightDelimiter = '}') {
        $searchArr  = array();
        $replaceArr = array();
        $i = 0;
        if (is_array($placeHolders)) {
            foreach ($placeHolders as $placeHolder => $replace) {
                $searchArr[$i]  = "{$leftDelimiter}{$placeHolder}{$rightDelimiter}";
                $replaceArr[$i] = strval($replace);
                $i ++;
            }
        }
        return str_replace($searchArr, $replaceArr, $target);
    }

    public static function is($pattern, $value)
    {
        if ($pattern == $value) {
            return true;
        }

        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern);

        return (bool) preg_match('#^'.$pattern.'\z#', $value);
    }

    /**
     * isValidIDCard 
     * 检查是否有效身份证号
     * @param mixed $idcard 
     * @static
     * @access public
     * @return void
     */
    public static function isValidIDCard($idcard) {
        return preg_match('/^\d{17}[xX\d]$/', $idcard) || preg_match('/^\d{15}$/i', $idcard);
    }
}
