<?php

namespace Model;

class Filters {
    use \Nette\SmartObject;
    /**
     * loader
     * @param filtr name
     * @return mixed
     */
    public static function loader($filter) {
        return (method_exists(__CLASS__, $filter) ? call_user_func_array([__CLASS__, $filter], array_slice(func_get_args(), 1)) : null);
    }

    /**
     * toUTF
     * @param string
     * @return Nette\Utils\Html
     */
    public static function toUTF($s) {
        return(iconv("CP1250", "UTF-8", $s));
    }
    
    /**
     * m3Time
     * @param string
     * @return Nette\Utils\Html
     */
    public static function timeFromNumber($s) {
        if ($s == '') {
            return('');
        } else {
            $s = floatval($s);
            $sign = $s >= 0 ? '' : '-';
            $s = abs($s);
            return($sign . floor($s) . ':' . str_pad(round(($s - floor($s)) * 60), 2, '0' , STR_PAD_LEFT));
        }
    }
    
    /**
     * czDate
     * @param int
     * @return Nette\Utils\Html
     */
    public static function czDate($s) {
        return(date("j.n.Y", $s));
    }
    
    /**
     * czDate
     * @param int
     * @return Nette\Utils\Html
     */
    public static function czDateS($s) {
        return(date("j.n.y", $s));
    }
    
    /**
     * czDateO
     * @param \DateTime
     * @return Nette\Utils\Html
     */
    public static function czDateO($s) {
        return(is_null($s) ? '' : date("j.n.Y", $s->getTimestamp()));
    }
    
    /**
     * czDateO
     * @param \DateTime
     * @return Nette\Utils\Html
     */
    public static function czDateSO($s) {
        return(is_null($s) ? '' : date("j.n.y", $s->getTimestamp()));
    }
    
    /**
     * czDateTime
     * @param int
     * @return Nette\Utils\Html
     */
    public static function czDateTime($s) {
        return(is_null($s) ? '' : date("j.n.Y H:i:s", $s));
    }
    
    /**
     * czDateTime
     * @param int
     * @return Nette\Utils\Html
     */
    public static function czDateTimeO($s) {
        return(is_null($s) ? '' : date("j.n.Y H:i:s", $s->getTimestamp()));
    }
    
    /**
     * czDateTime
     * @param int
     * @return Nette\Utils\Html
     */
    public static function czDateTimeSO($s) {
        return(is_null($s) ? '' : date("j.n/y H:i", $s->getTimestamp()));
    }
    
    /**
     * czDateTime
     * @param int
     * @return Nette\Utils\Html
     */
    public static function czDateTimeSSO($s) {
        return(is_null($s) ? '' : date("j.n H:i", $s->getTimestamp()));
    }
    
    /**
     * number without decimal
     * @param int
     * @return Nette\Utils\Html
     */
    public static function intNumber($s) {
        return(is_null($s) ? '' : number_format($s, 0, '.', '`'));
    }
    
    /**
     * number with 2 decimals
     * @param float
     * @return Nette\Utils\Html
     */
    public static function floatNumber($s) {
        return(is_null($s) ? '' : number_format($s, 2, '.', '`'));
    }
    public static function float2Number($s) {
        return(is_null($s) ? '' : number_format($s, 2, '.', '`'));
    }
    public static function float1Number($s) {
        return(is_null($s) ? '' : number_format($s, 1, '.', '`'));
    }
    public static function float3Number($s) {
        return(is_null($s) ? '' : number_format($s, 3, '.', '`'));
    }
    public static function float6Number($s) {
        return(is_null($s) ? '' : number_format($s, 6, '.', '`'));
    }
    

}
