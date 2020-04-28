<?php

    namespace Colibri\Xml {

        /**
         * Список узлов
         * 
         * @property-read \DOMDocument $document
         */
        class XmlNodeList implements \IteratorAggregate {

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

            public function __construct(\DOMNodeList $nodelist, \DOMDocument $dom) {
                $this->_data = $nodelist;
                $this->_document = $dom;
            }

            public function getIterator() {
                return new XmlNodeListIterator($this);
            }

            public function Item($index) {
                if($this->_data->item($index)){
                    return new XmlNode($this->_data->item($index), $this->_document);
                }
                return null;
            }

            public function __get($property) {
                if (strtolower($property) == 'document') {
                    return $this->_document;
                }
                return null;
            }

            public function Count() {
                return $this->_data->length;
            }

            public function First() {
                return $this->Item(0);
            }

            public function Last() {
                return $this->Item($this->Count()-1);
            }

            public function Remove() {
                foreach($this as $d) {
                    $d->Remove();
                }
            }

            public function ToObject($exclude = array()) {
                $ret = array();

                foreach ($this as $child) {
                    if (in_array($child->name, $exclude)) {
                        continue;
                    }
                    if(!isset($ret[$child->name])) {
                        $ret[$child->name] = [];
                    }
                    $ret[$child->name][] = $child->ToObject($exclude);
                }

                foreach ($this as $child) {
                    if(count($ret[$child->name]) == 1) {
                        $ret[$child->name] = $ret[$child->name][0];
                    }
                }

                if(!count($ret)) {
                    return null;
                }

                return count($ret) == 1 ? $ret[array_keys($ret)[0]] : $ret;
            }


        }

    }