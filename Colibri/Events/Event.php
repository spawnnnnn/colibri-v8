<?php

    /**
     * Класс события
     * 
     * @author Ваган Григорян <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Events
     * @version 1.0.0
     * 
     * 
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