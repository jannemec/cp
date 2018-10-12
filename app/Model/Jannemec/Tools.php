<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model\Jannemec;

/**
 * Description of Tools
 *
 * @author Nemec
 */
class Tools {
    
    private static function getMimeType($filepath) {
        if(!preg_match('/\.[^\/\\\\]+$/',$filepath)) {
            return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filepath);
        }
        switch(strtolower(preg_replace('/^.*\./','',$filepath))) {
        // START MS Office 2007 Docs
        case 'doc':
            return 'application/msword';
        case 'docx':
            return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        case 'docm':
            return 'application/vnd.ms-word.document.macroEnabled.12';
        case 'dotx':
            return 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
        case 'dotm':
            return 'application/vnd.ms-word.template.macroEnabled.12';
        case 'xlsx':
            return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        case 'xlsm':
            return 'application/vnd.ms-excel.sheet.macroEnabled.12';
        case 'xltx':
            return 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
        case 'xltm':
            return 'application/vnd.ms-excel.template.macroEnabled.12';
        case 'xlsb':
            return 'application/vnd.ms-excel.sheet.binary.macroEnabled.12';
        case 'xlam':
            return 'application/vnd.ms-excel.addin.macroEnabled.12';
        case 'pptx':
            return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
        case 'pptm':
            return 'application/vnd.ms-powerpoint.presentation.macroEnabled.12';
        case 'ppsx':
            return 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
        case 'ppsm':
            return 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12';
        case 'potx':
            return 'application/vnd.openxmlformats-officedocument.presentationml.template';
        case 'potm':
            return 'application/vnd.ms-powerpoint.template.macroEnabled.12';
        case 'ppam':
            return 'application/vnd.ms-powerpoint.addin.macroEnabled.12';
        case 'sldx':
            return 'application/vnd.openxmlformats-officedocument.presentationml.slide';
        case 'sldm':
            return 'application/vnd.ms-powerpoint.slide.macroEnabled.12';
        case 'one':
            return 'application/msonenote';
        case 'onetoc2':
            return 'application/msonenote';
        case 'onetmp':
            return 'application/msonenote';
        case 'onepkg':
            return 'application/msonenote';
        case 'thmx':
            return 'application/vnd.ms-officetheme';
            //END MS Office 2007 Docs

        }
        if (file_exists($filepath)) {
            return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filepath);
        } else {
            return(null);
        }
    }
    
    public static function getStyleForDocType($doctype) {
        switch(trim($doctype)) {
            case 'doc':
            case 'docx':
            case 'dotx':
            case 'dotm':
            case 'rtf':
            case 'dot':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            case 'application/vnd.ms-word.document.macroEnabled.12':
            case 'application/msword':
                return('iconfile doc');
                break;
            case 'xls':
            case 'xlsx':
            case 'xlsm':
            case 'xltx':
            case 'xlam':
            case 'xlsb':
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            case 'application/vnd.ms-excel.sheet.macroEnabled.12':
                return('iconfile xls');
                break;
            case 'pdf':
            case 'application/pdf':
                return('iconfile pdf');
                break;
            case 'jpg':
            case 'jpeg':
                return('iconfile jpg');
                break;
            case 'png':
                return('iconfile png');
                break;
            case 'gif':
                return('iconfile gif');
                break;
            case 'ppt':
            case 'pptx':
            case 'pptm':
            case 'potx':
            case 'potm':
            case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                return('iconfile ppt');
                break;
            case 'txt':
            case 'log':
                return('iconfile txt');
                break;
            case 'xml':
                return('iconfile xml');
                break;
            case 'avi':
            case 'mov':
                return('iconfile avi');
                break;
            case 'zip':
                return('iconfile zip');
                break;
            case 'dwg':
                return('iconfile dwg');
                break;
            default: 
                return('iconfile file');
                break;
        }
    }
    
    
    // Konverze znakových sad
    public static function win2utf($str, $ignore = false) {
        if ($ignore) {
            return(iconv("CP1250", "UTF-8//IGNORE", $str));
        } else {
            return(iconv("CP1250", "UTF-8//IGNORE", $str));
            //return(iconv("CP1250", "UTF-8", $str));
        }
    }
    
    public static function win2utf16($str) {
        return(iconv("CP1250", "UTF-16", $str));
    }
    
    public static function win2latin1($str) {
        return(iconv("CP1250", "CP850", $str));
    }
    
    public static function win2utfArr($arr, $ignore = false) {
        foreach($arr as $key => $val) {
            if (is_string($val)) {
                $arr[$key] = self::win2utf($val, $ignore);
            }
        }
        return($arr);
    }

    public static function utf2win($str, $ignore = false) {
        if ($ignore) {
            return(iconv("UTF-8", "CP1250//IGNORE", $str));
        } else {
            return(iconv("UTF-8", "CP1250", $str));
        }
    }

    public static function iso2utf($str) {
        return(iconv("ISO-8859-1", "UTF-8//IGNORE", $str));
    }

    public static function utf2iso($str) {
        return(iconv("UTF-8", "ISO-8859-1//IGNORE", $str));
    }
    
    public static function latin12utf($str) {
        return(iconv("CP850", "UTF-8//IGNORE", $str));
    }
    
    public static function utf2latin1($str) {
        return(iconv("UTF-8", "CP850//IGNORE", $str));
    }

    public static function ansi2utf($str) {
        return(iconv("TIS-620", "UTF-8//IGNORE", $str));
    }

    public static function utf2ansi($str) {
        return(iconv("UTF-8", "TIS-620", $str));
    }
    
    public static function utf2ascii($str) {
        $str = StrTr($str, ['ů' => 'u', 'ö' => 'o', 'Å' => 'A', 'a' => 'a']);
        return(strtr(iconv("UTF-8", "ASCII//TRANSLIT", $str), array('\'' => '')));
        //$string=iconv('utf-8','windows-1250',$str);
        $win = "ěščřžýáíéťňďúůóöüäĚŠČŘŽÝÁÍÉŤŇĎÚŮÓÖÜËÄ\x97\x96\x91\x92\x84\x93\x94\xAB\xBB";
        $ascii="escrzyaietnduuoouaESCRZYAIETNDUUOOUEAOUEA\x2D\x2D\x27\x27\x22\x22\x22\x22\x22";
        $string = StrTr($str,$win,$ascii);
        return $string;
    }
}
