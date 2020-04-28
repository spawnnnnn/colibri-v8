<?php
    /**
     * Обьект в html и обратно
     * 
     * @author Ваган Григорян <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Helpers
     * @version 1.0.0
     * 
     */
    namespace Colibri\Helpers {

        /**
         * Обьект в html и обратно
         */
        class Objects
        {

            public static function IsAssociativeArray($array) {
                $keys = array_keys($array);
                foreach($keys as $key) {
                    if(!is_numeric($key)) {
                        return true;
                    }
                }
                return false;
            }

            public static function ArrayToObject($array) {
                if (is_object($array)) {
                    $array = get_object_vars($array);
                }
                if (is_array($array)) {
                    foreach ($array as $k=>$v) {
                        if(is_array($v) && self::IsAssociativeArray($v)) {
                            $array[$k] = self::ArrayToObject($v);
                        }
                        else {
                            $array[$k] = $v;
                        }
                    }
                    $array = (object) $array;
                }
                return $array;
            }

        }
    }

?>