<?php
    /**
     * Xml
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2020 ColibriLab
     * @package Colibri\Xml
     *
     */
    namespace Colibri\Xml {

        use Colibri\Helpers\Strings;

        /**
         * Список атрибутов
         * @property-read int $count
         */
        class XmlNodeAttributeList implements \IteratorAggregate {

            /**
             * Документ
             *
             * @var \DOMDocument
             */
            private $_document;

            /**
             * Нода
             *
             * @var mixed
             */
            private $_node;

            /**
             * Список атрибутов
             *
             * @var \DOMNamedNodeMap
             */
            private $_data;

            /**
             * Конструктор
             *
             * @param \DOMDocument $document документ
             * @param \DOMNode $node узел
             * @param \DOMNamedNodeMap $xmlattributes список атрибутов
             */
            public function __construct(\DOMDocument $document, \DOMNode $node, \DOMNamedNodeMap $xmlattributes) {
                $this->_document = $document;
                $this->_node = $node;
                $this->_data = $xmlattributes;
            }

            /**
             * Возвращает итератор для обхода методом foreach
             *
             * @return XmlNodeListIterator
             */
            public function getIterator() {
                return new XmlNodeListIterator($this);
            }

            /**
             * Возвращает атрибут по индексу
             *
             * @param int $index
             * @return XmlAttribute
             */
            public function Item($index) {
                return $this->_data->item($index);
            }

            /**
             * Возвращает количество атрибутов
             *
             * @return int
             */
            public function Count() { 
                return $this->_data->Count();
            }

            /**
             * Геттер
             *
             * @param string $property
             * @return XmlAttribute|null
             */
            public function __get($property) {

                $attr = $this->_data->getNamedItem(Strings::FromCamelCaseAttr($property));
                if(!is_null($attr)){
                    return new XmlAttribute($attr);
                }
                return null;

            }

            /**
             * Добавляет атрибут
             *
             * @param string $name название атрибута
             * @param string $value значение атрибута
             * @return void
             */
            public function Append($name, $value) {
                $attr = $this->_document->createAttribute($name);
                $attr->value = $value;
                $this->_node->appendChild($attr);
            }

            /**
             * Удаляет аттрибут по имени
             *
             * @param string $name название атрибута
             * @return void
             */
            public function Remove($name) {
                if($this->$name && $this->$name->raw){
                    $this->_node->removeAttributeNode($this->$name->raw);
                }
            }



        }

    }