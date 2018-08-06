<?php
namespace Ice\Util;
class DMoney {
    
    /**
     * 分转元
     * @param $price
     * @return number
     */
    public static function penny2yuan($price){
        return strval(round($price/100,2));
    }
    
    /**
     * 元转分
     * @param $price
     * @return number
     */
    public static function yuan2penny($price){
        return intval(round($price * 100));
    }

}
