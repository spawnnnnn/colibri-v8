<?php
    /**
     * Request
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Request
     */
    namespace Colibri\IO\Request {

        use Colibri\Collections\ArrayList;

        /**
         * Данные запроса
         */
        class Data extends ArrayList
        {

            /**
             * Создать данные из массива
             *
             * @param mixed $array
             * @return Data
             */
            public static function FromArray($array)
            {
                $d = new Data();
                foreach ($array as $k => $v) {
                    $d->Add(new DataItem($k, $v));
                }
                return $d;
            }
        }

    }
