<?php

    namespace Colibri\Helpers {
    
    
        class Encoding
        {
            const UTF8 = "utf-8";
            const CP1251 = "windows-1251";
        
            public static function Convert($string, $to, $from = false)
            {
                $isArray = false;
                if (is_array($string)) {
                    $isArray = true;
                    $string = serialize($string);
                }
                if (!$from) {
                    $from = Encoding::Detect($string);
                }
                $to = strtolower($to);
                $from = strtolower($from);
                if ($from != $to) {
                    $return = mb_convert_encoding($string, $to, $from);
                } else {
                    $return = $string;
                }
                return $isArray ? unserialize($return) : $return;
            }
        
            public static function Check($string, $encoding)
            {
                return mb_check_encoding($string, strtolower($encoding));
            }
        
            public static function Detect($string)
            {
                return strtolower(mb_detect_encoding($string));
            }
        }
    
    }

?>