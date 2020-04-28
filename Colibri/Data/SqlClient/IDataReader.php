<?php

    /**
     * Интерфейсы для драйверов к базе данных
     *
     * @author Ваган Григорян <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Utils\Config
     * @version 1.0.0
     *
     */
    namespace Colibri\Data\SqlClient {

        /**
         * Интерфейс для всех классов DataReader в точке доступа
         */
        interface IDataReader
        {
            /**
             * Возвращает список полей в запросе
             *
             * @return array(string)
             */
            public function Fields();

            /**
             * Считывает следующую строку и возвращет в виде обьекта
             *
             * @return stdClass
             */
            public function Read();

            /**
             * Закрывает ресурс запроса
             *
             * @return void
             */
            public function Close();

            /**
             * Возвращает количество строк в текущей странице
             *
             * @return int
             */
            public function Count();
            
            /**
             * Возвращает общее количество строк
             *
             * @return int
             */
            public function Affected();

            /**
             * Возвращает наличие строк
             *
             * @return bool
             */
            public function HasRows();
            
            
        }

    }

?>
