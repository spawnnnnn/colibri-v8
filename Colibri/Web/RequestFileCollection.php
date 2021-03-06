<?php
    /**
     * Web
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Web
     * 
     * 
     */
    namespace Colibri\Web {

        /**
         * Коллекция файлов запроса
         * Readonly
         */
        class RequestFileCollection extends RequestCollection
        {
            /**
             * Магический метод
             *
             * @param string $property свойство
             * @return RequestedFile выбранный файл или null
             */
            public function __get($property)
            {
                return new RequestedFile(parent::__get($property));
            }
        }
        
    }