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
         * Обертки на всякие разные функции PHP
         */
        class Variable
        {
            
            /**
             * Проверить пустое ли значение в переменной
             *
             * @param mixed $var
             * @return boolean
             */
            public static function IsEmpty($var)
            {
                if (is_object($var)) {
                    return is_null($var);
                }
                return ($var === null || $var === "");
            }
            
            /**
             * Проверка на NULL
             *
             * @param mixed $var
             * @return boolean
             */
            public static function IsNull($var)
            {
                return is_null($var);
            }
            
            /**
             * Проверить обьект ли в переменной
             *
             * @param mixed $var
             * @return boolean
             */
            public static function IsObject($var)
            {
                return is_object($var);
            }
            
            /**
             * Проверить массив ли в переменной
             *
             * @param mixed $var
             * @return boolean
             */
            public static function IsArray($var)
            {
                return is_array($var);
            }
            
            /**
             * Проверка на true/false
             *
             * @param mixed $var
             * @return boolean
             */
            public static function IsBool($var)
            {
                return is_bool($var);
            }
            
            /**
             * Проверить не строка ли в переменной
             *
             * @param mixed $var
             * @return boolean
             */
            public static function IsString($var)
            {
                return is_string($var);
            }
            
            /**
             * Проверить не число ли в переменной
             *
             * @param mixed $var
             * @return boolean
             */
            public static function IsNumeric($var)
            {
                return is_numeric($var);
            }
            
            /**
             * Проверить не дата ли в переменной
             *
             * @param mixed $var
             * @return boolean
             */
            public static function IsDate($var)
            {
                if (!$var || is_null($var)) {
                    return false;
                }

                if (is_string($var)) {
                    return strtotime($var) !== false;
                } 
                
                return true;

            }
            
            /**
             * Проверить не время ли в переменной
             *
             * @param mixed $value
             * @return boolean
             */
            public static function IsTime($value)
            {
                if (preg_match('/\d{2}:\d{2}/', $value, $matches) > 0) {
                    return true;
                }
                return false;
            }

            /**
             * Изменить регистр значений
             *
             * @param array $array
             * @param int $case
             * @return array
             */
            public static function ChangeArrayValueCase($array, $case = CASE_LOWER)
            {
                for ($i=0; $i<count($array); $i++) {
                    $array[$i] = $case == CASE_LOWER ? Strings::ToLower($array[$i]) : Strings::ToUpper($array[$i]);
                }
                return $array;
            }

            /**
             * Изменить регистр ключей
             *
             * @param array $array
             * @param int $case
             * @return array
             */
            public static function ChangeArrayKeyCase($array, $case = CASE_LOWER)
            {
                return array_change_key_case($array, $case);
            }
            
            /**
             * Превратить обьект в массив рекурсивно
             *
             * @param stdClass $object
             * @return array
             */
            public static function ObjectToArray($object)
            {
                if (Variable::IsObject($object)) {
                    $object = get_object_vars($object);
                    
                    foreach ($object as $k => $v) {
                        $array[$k] = self::ObjectToArray($v);
                    }
                }

                return $object;
            }
            
            /**
             * Превратить массив в обьект рекурсивно
             *
             * @param array $array
             * @return stdClass
             */
            public static function ArrayToObject($array)
            {
                if (Variable::IsObject($array)) {
                    $array = get_object_vars($array);
                }
                if (is_array($array)) {
                    foreach ($array as $k=>$v) {
                        $array[$k] = self::ArrayToObject($v);
                    }
                    $array = (object) $array;
                }
                return $array;
            }

            /**
             * Проверяет ассоциативный ли массив
             *
             * @param array $array
             * @return boolean
             */
            public static function IsAssociativeArray($array)
            {
                if (!is_array($array)) {
                    return false;
                }

                $keys = array_keys($array);
                foreach ($keys as $key) {
                    if (!is_numeric($key)) {
                        return true;
                    }
                }
                return false;
            }

            /**
             * Превратить текст в 16-ричное представление
             *
             * @param string $data
             * @return string
             */
            public static function Bin2Hex($data)
            {
                return bin2hex($data);
            }
            
            /**
             * Из 16-ричного в обычный текст
             *
             * @param string $data
             * @return string
             */
            public static function Hex2Bin($data)
            {
                $len = strlen($data);
                try {
                    return pack("H" . $len, $data);
                } catch (\Exception $e) {
                    return '';
                }
            }
            
            /**
             * Проверить сериализованный ли это обьект
             *
             * @param string $v
             * @return boolean
             */
            public static function isSerialized($v)
            {
                $vv = @unserialize($v);
                if (is_array($vv) || is_object($vv)) {
                    return true;
                }
                return false;
            }
            
            /**
             * Сериализовать в строку
             *
             * @param mixed $obj
             * @return string
             */
            public static function Serialize($obj)
            {
                return '0x'.Variable::Bin2Hex(serialize($obj));
            }

            /**
             * Десериализовать из строки
             *
             * @param string $string
             * @return mixed
             */
            public static function Unserialize($string)
            {
                if (substr($string, 0, 2) == '0x') {
                    $string = Variable::Hex2Bin(substr($string, 2));
                }
                return @unserialize($string);
            }
            
            /**
             * Создает ключ массива строк
             * md5([md5(value1).md5(value2)...])
             *
             * @param array $array
             * @return string
             */
            public static function CreateHash($array)
            {
                $a = Variable::FillArray($array);
                
                $rret = '';
                foreach ($a as $b) {
                    $rret = $rret == '' ? md5($b) : $rret & md5($b);
                }
                
                return md5($rret);
            }
            
            /**
             * Шифрование
             *
             * @param array $items
             * @param array $perms
             * @return string
             */
            public static function FillArray($items, $perms = array())
            {
                static $retperms = array();
                if (!empty($items)) {
                    for ($i = count($items) - 1; $i >= 0; --$i) {
                        $newitems = $items;
                        $newperms = $perms;
                        list($foo) = array_splice($newitems, $i, 1);
                        array_unshift($newperms, $foo);
                        Variable::FillArray($newitems, $newperms);
                    }
                } else {
                    $retperms[] = $perms;
                }
                
                $a = array();
                foreach ($retperms as $b) {
                    $a[] = join('', $b);
                }
                
                return $a;
            }

            /**
             * Копирует 2 обьекта/массива в один, с заменой существующий значений
             * Аналог jQuery.extend
             *
             * @param mixed $o1
             * @param mixed $o2
             * @return mixed
             */
            public static function Extend($o1, $o2)
            {
                $o1 = (array)$o1;
                $o2 = (array)$o2;
                
                foreach ($o1 as $k => $v) {
                    if (isset($o2[$k])) {
                        $o1[$k] = $o2[$k];
                    }
                }
                
                foreach ($o2 as $k => $v) {
                    if (!isset($o1[$k])) {
                        $o1[$k] = $v;
                    }
                }
                
                return $o1;
            }
            
            /**
             * Проверяет если d=null то возвращает def
             *
             * @param mixed $d
             * @param mixed $def
             * @return mixed
             */
            public static function Coalesce($d, $def)
            {
                if (is_null($d)) {
                    return $def;
                }
                return $d;
            }
            
            /**
             * Собирает массив/обьект в строку
             *
             * @param mixed $object
             * @param string $spl1
             * @param string $spl2
             * @param boolean $quote
             * @param string $keyPrefix
             * @return string
             */
            public static function ToString($object, $spl1 = ' ', $spl2 = '=', $quote = true, $keyPrefix = '')
            {
                $ret = array();
                $object = (array)$object;
                foreach ($object as $k => $v) {
                    $ret[] = $keyPrefix.$k.$spl2.($quote ? '"' : '').Strings::PrepareAttribute($v).($quote ? '"' : '');
                }
                return implode($spl1, $ret);
            }

            /**
             * Возвращает обьект из вывода var_dump
             * @param string $string
             */
            public static function FromPhpArrayOutput($string)
            {
                $ret = array();
                $lines = explode("\n", $string);
                foreach ($lines as $line) {
                    if (trim($line, "\r\t\n ") === '') {
                        continue;
                    }
                    
                    $parts = explode("=>", trim($line, "\r\t\n "));

                    $value = end($parts);
                    $key = reset($parts);
                    $key = trim($key, "[] ");
                    $ret[$key] = $value;
                }
                
                return $ret;
            }
        }

    }
