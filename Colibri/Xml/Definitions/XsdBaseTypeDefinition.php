<?php
    /**
     * Definitions
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2020 ColibriLab
     * @package Colibri\Xml\Definitions
     *
     */
    namespace Colibri\Xml\Definitions {

        /**
         * Определени простого типа
         * 
         * @property-read string $name
         * @property-read stdClass $restrictions
         */
        class XsdBaseTypeDefinition implements \JsonSerializable {

            /**
             * Базовый тип
             *
             * @var string
             */
            private $_base;

            /**
             * Конструктор
             *
             * @param string $base базовый тип
             */
            public function __construct($base)
            {
                $this->_base = $base;
            }

            /**
             * Геттер
             *
             * @param string $name
             * @return mixed
             */
            public function __get($name)
            {
                if(strtolower($name) == 'name') {
                    return str_replace('xs:', '', $this->_base);
                }
                else if(strtolower($name) == 'restrictions') {
                    return (object)['base' => $this->name];
                }
            }

            /**
             * Возвращает данные в виде простого обьекта для упаковки в json
             *
             * @return stdClass
             */
            public function jsonSerialize()
            {
                return (object)array('name' => $this->name, 'restrictions' => $this->restrictions);
            }

            /**
             * Возвращает данные в виде простого обьекта
             *
             * @return stdClass
             */
            public function ToObject()
            {
                return (object)array('name' => $this->name, 'restrictions' => $this->restrictions);
            }

        }
    }