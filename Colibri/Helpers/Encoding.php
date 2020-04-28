<?php
    /**
     * Helpers
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Helpers
     */
    namespace Colibri\Helpers {
    
        /**
         * Работа с кодировками
         */
        class Encoding
        {
            const UTF8 = "utf-8";
            const CP1251 = "windows-1251";
        
            /**
             * Конвертирует кодировку
             *
             * @param string $string строка для кодирования
             * @param string $to кодировка в которую нужно перекодировать
             * @param string $from кодировка с которой нужно кодировать
             * @return string перекодированная строка
             */
            public static function Convert($string, $to, $from = null)
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
        
            /**
             * Проверить кодировку
             *
             * @param string $string строка для проверки
             * @param string $encoding кодировка
             * @return bool
             */
            public static function Check($string, $encoding)
            {
                return mb_check_encoding($string, strtolower($encoding));
            }
        
            /**
             * Получает кодировку строки, если возможно
             *
             * @param string $string строка для получения кодировки
             * @return string
             */
            public static function Detect($string)
            {
                return strtolower(mb_detect_encoding($string));
            }
        }
    
    }
