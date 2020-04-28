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
         * Всякие разные виды рандомизации
         */
        class Randomization
        {
            
            /**
             * Вернуть новый SEED
             *
             * @return integer
             */
            public static function Seed()
            {
                list($usec, $sec) = explode(' ', microtime());
                return (float) $sec + ((float) $usec * 100000);
            }

            /**
             * Рандомное значение между макс и мин
             *
             * @param integer $min
             * @param integer $max
             * @return integer
             */
            public static function Integer($min, $max)
            {
                return rand($min, $max);
            }

            /**
             * Указанное количество рандомных символов
             *
             * @param integer $l
             * @return string
             */
            public static function Mixed($l)
            {
                $j = 0;
                $tmp = "";
                $c = array();
                $i = 0;
                
                for ($j = 1; $j < $l; $j++) {
                    $i = (int) Randomization::Integer(0, 2.999999);
                    $c[0] = chr((int) Randomization::Integer(ord("A"), ord("Z")));
                    $c[1] = chr((int) Randomization::Integer(ord("a"), ord("z")));
                    $c[2] = chr((int) Randomization::Integer(ord("0"), ord("9")));
                    $tmp = $tmp.$c[$i];
                }

                return $tmp;
            }

            /**
             * Указанное количество произвольных цифр
             *
             * @param integer $l
             * @return string
             */
            public static function Numeric($l)
            {
                $j = 0;
                $tmp = "";
                $c = array();
                $i = 0;
                
                for ($j = 1; $j <= $l; $j++) {
                    $i = (int) Randomization::Integer(0, 2.999999);
                    $c[0] = chr((int) Randomization::Integer(ord("0"), ord("9")));
                    $c[1] = chr((int) Randomization::Integer(ord("0"), ord("9")));
                    $c[2] = chr((int) Randomization::Integer(ord("0"), ord("9")));
                    $tmp = $tmp.$c[$i];
                }

                return $tmp;
            }

            /**
             * Указанное количество рандомных символов - без цифр
             *
             * @param integer $l
             * @return string
             */
            public static function Character($l)
            {
                $tmp = "";
                $c = array();
                
                for ($i = 0; $i < $l; $i++) {
                    $j = (int) rand(0, 1);
                    $c[0] = chr((int) Randomization::Integer(ord("A"), ord("Z")));
                    $c[1] = chr((int) Randomization::Integer(ord("a"), ord("z")));
                    $tmp = $tmp.$c[$j];
                }

                return $tmp;
            }
        }

    }
