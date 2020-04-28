<?php

    /**
     * Configuration
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Configuration
     *
     */
    namespace Colibri\Configuration\Drivers {

        /**
         * Интерфейс для драйверов кофигурации
         */
        interface IConfigDriver {

            /**
             * Считает данные
             *
             * @param string $query
             * @param mixed $default
             * @return mixed
             */
            public function Read(string $query, $default = null);

            /**
             * Пишет данные
             *
             * @param string $query
             * @param mixed $value
             * @return bool
             */
            public function Write(string $query, $value);
            
            /**
             * Вернуть внутренние данные в виде обьекта
             *
             * @return stdClass
             */
            public function AsObject();

            /**
             * Вернуть внутренние данные в виде массива
             *
             * @return array
             */
            public function AsArray();

            /**
             * Вернуть внутренние данные в исходном виде
             *
             * @return array
             */
            public function AsRaw();

            /**
             * Вернуть хранимое значение
             * Внимание! Если текущие данные массив или обьект, то будет возвращен null
             *
             * @return mixed
             */
            public function GetValue();

        }


    }