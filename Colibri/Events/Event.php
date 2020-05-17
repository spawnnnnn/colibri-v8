<?php

    /**
     * Events
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Events
     */
    namespace Colibri\Events {

        /**
         * Класс событие
         *
         * @property-read string $name
         * @property-read mixed $sender
         *
         */
        class Event
        {
                
            /**
             * Отправитель
             *
             * @var mixed
             */
            private $_sender;

            /**
             * Наименование события
             *
             * @var string
             */
            private $_name;

            /**
             * Конструктор
             *
             * @param mixed $sender
             * @param string $name
             */
            public function __construct($sender, $name)
            {
                $this->_sender = $sender;
                $this->_name = $name;
            }
        
            /**
             * Геттер
             *
             * @param string $key
             * @return mixed
             */
            public function __get($key)
            {
                $return = null;
                switch (strtolower($key)) {
                    case "name": {
                        $return = $this->_name;
                        break;
                    }
                    case "sender": {
                        $return = $this->_sender;
                        break;
                    }
                    default: {
                        $return = null;
                    }
                }
                return $return;
            }
        
        }

    }