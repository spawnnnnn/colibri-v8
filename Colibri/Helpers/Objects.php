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
         * Обьект в html и обратно
         */
        class Objects
        {
            /**
             * Проверка на ассоциативный массив
             *
             * @param mixed $array
             * @return bool
             */
            public static function IsAssociativeArray($array)
            {
                $keys = array_keys($array);
                foreach ($keys as $key) {
                    if (!is_numeric($key)) {
                        return true;
                    }
                }
                return false;
            }

            /**
             * Переделывает массив в обьект
             *
             * @param array $array
             * @return stdClass
             */
            public static function ArrayToObject($array)
            {
                if (is_object($array)) {
                    $array = get_object_vars($array);
                }
                if (is_array($array)) {
                    foreach ($array as $k=>$v) {
                        if (is_array($v) && self::IsAssociativeArray($v)) {
                            $array[$k] = self::ArrayToObject($v);
                        } else {
                            $array[$k] = $v;
                        }
                    }
                    $array = (object) $array;
                }
                return $array;
            }
        }
    }
