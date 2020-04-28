<?php
    /**
     * Строковые функции
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Helpers
     * 
     * 
     */
    namespace Colibri\Helpers {

        class Strings {

            /**
             * Unescape-ид строку
             *
             * @param string $s
             * @return string
             */
            public static function Unescape($s) {
                return preg_replace_callback(
                    '/% (?: u([A-F0-9]{1,4}) | ([A-F0-9]{1,2})) /sxi',
                    function ($p) {
                        $c = '';
                        if ($p[1]) {
                            $u = pack('n', hexdec($p[1]));
                            $c = @iconv('UCS-2BE', 'windows-1251', $u);
                        }
                        return $c;
                    },
                    $s
                );
            }

            /**
             * В прописные
             * 
             * @param string $s
             * @return string
             */
            public static function ToLower($s) {
                return mb_strtolower($s, "UTF-8");
            }
                                               
            /**
             * В заглавные
             *
             * @param string $s
             * @return string
             */
            public static function ToUpper($s) {
                return mb_strtoupper($s, "UTF-8");
            }
            
            /**
             * Первая заглавная осталвные прописные
             *
             * @param string $str
             * @return string
             */
            public static function ToUpperFirst($str) {
                return mb_strtoupper(mb_substr($str, 0, 1, "UTF-8"), "UTF-8").mb_substr($str, 1, mb_strlen($str, "UTF-8"), "UTF-8");
            }
            
            /**
             * Превратить строки из прописных с тире в кэмелкейс
             *
             * @param string $str
             * @param boolean $firstCapital
             * @return string
             */
            public static function ToCamelCaseAttr($str, $firstCapital = false) {
                if($firstCapital) {
                    $str = Strings::ToUpperFirst($str);
                }
                
                return preg_replace_callback('/\-([a-z])/', function($c) {
                    return Strings::ToUpper(substr($c[1], 0, 1)).Strings::ToLower(substr($c[1], 1));
                }, $str);
            }
            
            /**
             * Из кэмел кейса в прописные с тирешками, для использования в качестве названий аттрибутов
             *
             * @param string $str
             * @return string
             */
            public static function FromCamelCaseAttr($str) {
                return trim(preg_replace_callback('/([A-Z])/', function($c) {
                    return '-'.Strings::ToLower($c[1]);
                }, $str), '-');
            }
            
            /**
             * Из under_score в camelcase
             *
             * @param string $str
             * @param boolean $firstCapital
             * @return string
             */
            public static function ToCamelCaseVar($str, $firstCapital = false) {
                if($firstCapital) {
                    $str = Strings::ToUpperFirst($str);
                }
                
                return preg_replace_callback('/_([a-z])/', function($c) {
                    return Strings::ToUpper(substr($c[1], 0, 1)).Strings::ToLower(substr($c[1], 1));
                }, $str);
            }
            
            /**
             * Из CamelCase в under_score
             *
             * @param string $str
             * @return string
             */
            public static function FromCamelCaseVar($str) {
                return trim(preg_replace_callback('/([A-Z])/', function($c) {
                    return '_'.Strings::ToLower($c[1]);
                }, $str), '_');
            }
            
            /**
             * Проверяет на валидность электронного адреса
             *
             * @param string $address
             * @return boolean
             */
            public static function IsEmail($address) {
                if (function_exists('filter_var'))  {
                    return filter_var($address, FILTER_VALIDATE_EMAIL) !== false;
                }
                else {
                    return preg_match('/^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!\.)){0,61}[a-zA-Z0-9_-]?\.)+[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!$)){0,61}[a-zA-Z0-9_]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/', $address);
                }
            }
            
            /**
             * Проверяет на валидность URL адреса
             *
             * @param string $address
             * @return boolean
             */
            public static function IsUrl($address) {
                if (function_exists('filter_var'))  {
                    return filter_var($address, FILTER_VALIDATE_URL) !== false;
                }
                else {
                    return strstr($address, 'http://') !== false || strstr($address, 'https://') !== false || substr($address, 'ftp://') !== false || substr($address, '//') === 0;
                }
            }
            
            /**
             * Проверяет не заканчивается ли строка на заданную
             *
             * @param string $string
             * @param string $end
             * @return boolean
             */
            public static function EndsWith($string, $end) {
                return substr($string, strlen($string) - strlen($end)) == $end;
            }
            
            /**
             * Проверяет не налинается ли строка на заданную
             *
             * @param string $string
             * @param string $start
             * @return boolean
             */
            public static function StartsWith($string, $start) {
                return substr($string, 0, strlen($start)) == $start;
            }

            public static function StripHTML($html) {
                return strip_tags($html);
            }

            /**
             * Превращает url в виде hyphen-text в CamelCase Namespace
             *
             * @param string $url
             * @return string
             */
            public static function UrlToNamespace($url) {
                $class = explode('/', trim($url, '/'));
                $className = [];
                foreach($class as $name) {
                    $className[] = Strings::ToCamelCaseAttr($name, true);
                } 
                return implode('\\', $className);
            }

            public static function Substring($string, $start, $length = false) {
                $enc = mb_detect_encoding($string);
                if(!$length){
                    $length = mb_strlen($string, $enc);
                }
                return mb_substr($string, $start, $length, $enc);
            }

            public static function Length($string) {
                $encoding = mb_detect_encoding($string);
                if(!$encoding) { 
                    $encoding = 'utf-8';
                }
                return mb_strlen($string, $encoding);
            }

            public static function FormatSequence($secuence, $labels = array("год", "года", "лет"), $viewnumber = false) {

                $isfloat = intval($secuence) != floatval($secuence);
                $floatPoint = floatval($secuence) - intval($secuence);
                $floatPoint = $floatPoint.'';
                $floatPoint = str_replace('0.', '', $floatPoint);
                $floatLength = strlen($floatPoint);
    
                $s = "";
                if($viewnumber){
                    $s = $secuence." ";
                }
                $ssecuence = strval($secuence);
                $sIntervalLastChar = substr($ssecuence, strlen($ssecuence)-1, 1);
                if((int)$secuence > 10 && (int)$secuence < 20){
                    return $s.$labels[2]; //"лет"
                }
                else {
                    if(!$isfloat || $floatLength > 1) {
                        switch(intval($sIntervalLastChar)) {
                            case 1:
                                return $s.$labels[0];
                            case 2:
                            case 3:
                            case 4:
                                return $s.$labels[1];
                            case 5:
                            case 6:
                            case 7:
                            case 8:
                            case 9:
                            case 0:
                                return $s.$labels[2];
                            default: {
                                break;
                            }
                        }
                    }
                    else {
                        switch(intval($sIntervalLastChar)) {
                            case 1:
                                return $s.$labels[0];
                            case 2:
                            case 3:
                            case 4:
                            case 5:
                                return $s.$labels[1];
                            case 6:
                            case 7:
                            case 8:
                            case 9:
                            case 0:
                                return $s.$labels[2];
                            default: {
                                break;
                            }
                        }
                    }
                }
    
            }
    
            public static function FormatFileSize($number, $range = 1024, $postfixes = array("bytes", "Kb", "Mb", "Gb", "Tb")){
                for($j=0; $j < count($postfixes); $j++) {
                    if($number <= $range) {
                        break;
                    }
                    else {
                        $number = $number/$range;
                    }
                }
                $number = round($number, 2);
                return $number." ".$postfixes[$j];
            }

            public static function TrimLength($str, $length, $ellipsis = "...") {
                if(mb_strlen($str, "utf-8") > $length) {
                    return mb_substr($str, 0, $length-3, "UTF-8").$ellipsis;
                }
                else {
                    return $str;
                }
            }

            public static function Words($text, $n, $ellipsis = "...") {

                $text = Strings::StripHTML(trim($text));
                $a = preg_split("/ |,|\.|-|;|:|\(|\)|\{|\}|\[|\]/", $text);
    
                if (count($a) > 0) {
                    if(count($a) < $n){
                        return $text;
                    }
    
                    $l = 0;
                    for($j=0; $j<$n;$j++) {
                        $l = $l + mb_strlen($a[$j])+1;
                    }
    
                    return mb_substr($text, 0, $l).$ellipsis;
                }
                else {
                    return mb_substr($text, 0, $n);
                }
    
            }

            public static function Expand($s, $l, $c) {
                if( strlen($s) >= $l ){
                    return $s;
                }
                else {
                    return str_repeat($c, $l - strlen($s)).$s;
                }
            }

            public static function PrepareAttribute($string, $quoters = false) {
                if ($quoters) {
                    $string = preg_replace("/\'/", "&rsquo;", $string);
                }
                $string = preg_replace("/&amp;/", "&", $string);
                $string = preg_replace("/&nbsp;/", " ", $string);
                $string = preg_replace("/&/", "&amp;", $string);
                $string = preg_replace("/\n/", '', $string);
                return preg_replace("/\"/", "&quot;", $string);
            }

            public static function Randomize($length) {
                return Randomization::Mixed($length);
            }
    

        }

    }