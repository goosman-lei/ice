<?php
namespace Ice\Util;
class DNumber {
    /**
     * mapToOther
     * 将一个数字映射到另外一个数字, 保证不重复
     * @param mixed $num
     * @access public
     * @return void
     */
    public static function mapToOther($num) {
        $num += 10000;
        if ($num <= 0xFFFF) {
            $code = 0x10000 | ((0x00F0 & $num) << 8) | ((0x000F & $num) << 8) | ((0xF000 & $num) >> 8) | ((0x0F00 & $num) >> 8);
        } else if ($num <= 0xFFFFF) {
            $code = 0x100000 | ((0x000F0 & $num) << 12) | ((0x0000F & $num) << 12) | (0x00F00 & $num) | ((0xF0000 & $num) >> 12) | ((0x0F000 & $num) >> 12);
        } else if ($num <= 0xFFFFFF) {
            $code = ((0x000F00 & $num) << 12) | ((0x0000F0 & $num) << 12) | ((0x00000F & $num) << 12)
                | ((0xF00000 & $num) >> 12) | ((0x0F0000 & $num) >> 12) | ((0x00F000 & $num) >> 12)
                | 0x1000000;
        } else if ($num <= 0xFFFFFFF){
            $code = 0x10000000 |((0x0000F00 &$num) <<16) |((0x00000F0 &$num) <<16) |((0x000000F &$num) <<16) |(0x000F000 &$num) |((0xF000000 &$num) >>16) |((0x0F00000 &$num) >>16) | ((0x00F0000 &$num) >>16);
        } else {
            return FALSE;
        }
        return $code;
    }

    private static $chars = array('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v');

    private static $specialChars = array('w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    public static function mapToStr($num) {
        $bin = decbin($num);
        $ret = '';
        while(!empty($bin)){
            if(strlen($bin) <= 5){
                $ret = self::$chars[bindec($bin)].$ret;
                break;
            }
            $cur = substr($bin, strlen($bin)-5);
            $ret = self::$chars[bindec($cur)].$ret;
            $bin = substr($bin,0,strlen($bin)-5);
        }
        //
        $maxLen = 10;
        if(strlen($ret) < $maxLen){
            $prefixlen = mt_rand(1, $maxLen-strlen($ret));
            for($i=0;$i<$prefixlen;$i++){
                $char = self::$specialChars[mt_rand(0, 29)];
                $retLen = strlen($ret);
                $idx = mt_rand(0, $retLen);//将char插入任意位置
                if($idx == 0){
                    $ret = $char.$ret;
                } else if ($idx==$retLen){
                    $ret = $ret.$char;
                }else {
                    $ret = substr($ret, 0,$idx).$char.substr($ret, $idx);
                }
            }
        }
        return $ret;
    }
    
    /**
     * transferToNBase 
     * 将十进制数转换为N进制数值表示
     * @param mixed $charTable 字符表
     * @param mixed $number 待转换数值
     * @access public
     * @return void
     */
    function transferToNBase($charTable, $number) {
        $nbase  = strlen($charTable);
        $result = '';
        while ($number > 0) {
            $result = $charTable[bcmod($number, $nbase)] . $result;
            $number = bcdiv($number, $nbase);
        }
        return $result;
    }

    /**
     * transferFromNBase 
     * 将N进制数值表示转换为十进制
     * @param mixed $charTable 字符表
     * @param mixed $number 待转换数值
     * @access public
     * @return void
     */
    function transferFromNBase($charTable, $number) {
        $nbase  = strlen($charTable);
        $length = strlen($number);
        $offset = 0;
        $result = 0;
        while ($offset < $length) {
            $result += pow($nbase, $offset) * strpos($charTable, $number[$length - $offset - 1]);
            $offset ++;
        }
        return $result;
    }

    /**
     * intercrossEncode 
     * 数值按位交换编码
     * @param mixed $number 
     * @param int $maxOffset 
     * @access public
     * @return void
     */
    function intercrossEncode($number, $maxBit = 63) {
        $mask   = $maxBit >= 63 ? PHP_INT_MAX : pow(2, $maxBit) - 1;
        $binary = substr(sprintf("%064s", decbin(~($number & $mask))), 64 - $maxBit);
        return bindec(strrev($binary));
    }


}