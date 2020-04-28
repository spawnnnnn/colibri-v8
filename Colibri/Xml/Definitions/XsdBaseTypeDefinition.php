<?php

    namespace Colibri\Xml\Definitions {

        /**
         * Определени простого типа
         * 
         * @property-read string $name
         * @property-read stdClass $restrictions
         */
        class XsdBaseTypeDefinition implements \JsonSerializable {

            private $_base;

            public function __construct($base)
            {
                $this->_base = $base;
            }

            public function __get($name)
            {
                if(strtolower($name) == 'name') {
                    return str_replace('xs:', '', $this->_base);
                }
                else if(strtolower($name) == 'restrictions') {
                    return (object)['base' => $this->name];
                }
            }

            public function jsonSerialize()
            {
                return (object)array('name' => $this->name, 'restrictions' => $this->restrictions);
            }

            public function ToObject()
            {
                return (object)array('name' => $this->name, 'restrictions' => $this->restrictions);
            }

        }
    }