<?php

    namespace Colibri\Xml {

        /**
         * Класс для работы с атрибутами
         */
        class XmlAttribute {

            /**
             * Обьект содержающий DOMNode атрибута
             *
             * @var mixed
             */
            private $_data;

            /**
             * Конструктор
             *
             * @param \DOMNode $data
             */
            public function __construct(\DOMNode $data) {
                $this->_data = $data;
            }

            /**
             * Getter
             *
             * @param string $property
             * @return mixed
             */
            public function __get($property) {
                switch(strtolower($property)) {
                    case 'value':
                        return $this->_data->nodeValue;
                    case 'name':
                        return $this->_data->nodeName;
                    case 'type':
                        return $this->_data->nodeType;
                    case 'raw':
                        return $this->_data;
                    default: 
                        break;
                }
                return null;
            }

            /**
             * Setter
             *
             * @param string $property
             * @param string $value
             * @return void
             */
            public function __set($property, $value) {
                if(strtolower($property) == 'value') {
                        $this->_data->nodeValue = $value;
                }
            }

            /**
             * Удаляет атрибут
             *
             * @return void
             */
            public function Remove() {
                $this->_data->parentNode->removeAttributeNode($this->_data);
            }

        }

    }