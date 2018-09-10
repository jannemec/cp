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
     * m3Date
     * @param string
     * @return Nette\Utils\Html
     */
    public static function m3Date($s) {
        return(empty($s) ? '' : (ltrim(substr($s, 6, 2), '0') . '.' . ltrim(substr($s, 4, 2), '0') . '.' . substr($s, 0, 4)));
    }
    
    /**
     * m3Date
     * @param string
     * @return Nette\Utils\Html
     */
    public static function m3DateXML($s) {
        return(empty($s) ? '' : (substr($s, 0, 4) . '-' . substr($s, 4, 2) . '-' . substr($s, 6, 2)));
    }
    
    /**
     * m3Date
     * @param string
     * @return Nette\Utils\Html
     */
    public static function m3DateS($s) {
        return(empty($s) ? '' : (ltrim(substr($s, 6, 2), '0') . '.' . ltrim(substr($s, 4, 2), '0') . '.' . substr($s, 2, 2)));
    }
    
    public static function m3DateSS($s) {
        return(empty($s) ? '' : (ltrim(substr($s, 6, 2), '0') . '.' . ltrim(substr($s, 4, 2), '0')));
    }
    
    /**
     * m3Time
     * @param string
     * @return Nette\Utils\Html
     */
    public static function m3Time($s) {
        $s = str_pad(trim($s), 6, '0', STR_PAD_LEFT);
        return(empty($s) ? '' : ((ltrim(substr($s, 0, 2), ' 0') == '' ? '0' : ltrim(substr($s, 0, 2), ' 0')) . ':' . substr($s, 2, 2) . ':' . substr($s, 4, 2)));
    }
    
    /**
     * m3Time
     * @param string
     * @return Nette\Utils\Html
     */
    public static function m3TimeS($s) {
        $s = str_pad(trim($s), 6, '0', STR_PAD_LEFT);
        return(empty($s) ? '' : ((ltrim(substr($s, 0, 2), ' 0') == '' ? '0' : ltrim(substr($s, 0, 2), ' 0')) . ':' . substr($s, 2, 2)));
    }
    
    /**
     * m3Time
     * @param string
     * @return Nette\Utils\Html
     */
    public static function m3TimeMin($s) {
        $s = str_pad(trim($s), 4, '0', STR_PAD_LEFT);
        return(empty($s) ? '' : ((ltrim(substr($s, 0, 2), ' 0') == '' ? '0' : ltrim(substr($s, 0, 2), ' 0')) . ':' . substr($s, 2, 2)));
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
