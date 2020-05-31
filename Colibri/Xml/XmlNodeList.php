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

        /**
         * Список узлов
         *
         * @property-read \DOMDocument $document
         */
        class XmlNodeList implements \IteratorAggregate
        {

            /**
             * Список значений
             *
             * @var \DOMNodeList
             */
            private $_data;
            
            /**
             * Документ
             *
             * @var \DOMDocument
             */
            private $_document;

            /**
             * Конструктор
             *
             * @param \DOMNodeList $nodelist список узлов
             * @param \DOMDocument $dom документ
             */
            public function __construct(\DOMNodeList $nodelist, \DOMDocument $dom)
            {
                $this->_data = $nodelist;
                $this->_document = $dom;
            }

            /**
             * Возвращает итератор для обхода методом foreach
             *
             * @return XmlNodeListIterator
             */
            public function getIterator()
            {
                return new XmlNodeListIterator($this);
            }

            /**
             * Возвращает узел по индексу
             *
             * @param int $index
             * @return XmlNode|null
             */
            public function Item($index)
            {
                if ($this->_data->item($index)) {
                    return new XmlNode($this->_data->item($index), $this->_document);
                }
                return null;
            }

            /**
             * Геттер
             *
             * @param string $property
             * @return mixed
             */
            public function __get($property)
            {
                if (strtolower($property) == 'document') {
                    return $this->_document;
                }
                return null;
            }

            /**
             * Возвращает количество узлов
             *
             * @return int
             */
            public function Count()
            {
                return $this->_data->length;
            }

            /**
             * Возвращает первый узел
             *
             * @return XmlNode
             */
            public function First()
            {
                return $this->Item(0);
            }

            /**
             * Возвращает последний узел
             *
             * @return XmlNode
             */
            public function Last()
            {
                return $this->Item($this->Count()-1);
            }

            /**
             * Удаляет все узлы в коллекции
             *
             * @return void
             */
            public function Remove()
            {
                foreach ($this as $d) {
                    $d->Remove();
                }
            }

            /**
             * Возвращает все узлы в коллекции в виде обьекта
             *
             * @param array $exclude список названий атрибутов и узлов, которые нужно исключить
             * @return array|null
             */
            public function ToObject($exclude = array())
            {
                $ret = array();

                foreach ($this as $child) {
                    if (in_array($child->name, $exclude)) {
                        continue;
                    }
                    if (!isset($ret[$child->name])) {
                        $ret[$child->name] = [];
                    }
                    $ret[$child->name][] = $child->ToObject($exclude);
                }

                foreach ($this as $child) {
                    if (count($ret[$child->name]) == 1) {
                        $ret[$child->name] = $ret[$child->name][0];
                    }
                }

                if (!count($ret)) {
                    return null;
                }

                return count($ret) == 1 ? $ret[array_keys($ret)[0]] : $ret;
            }
        }

    }
