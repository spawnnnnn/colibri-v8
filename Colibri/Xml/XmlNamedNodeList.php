<?php
    /**
     * Xml
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Xml
     * 
     */
    namespace Colibri\Xml {

        use Colibri\Collections\ReadonlyCollection;

        /**
         * Список узлов
         * @property-read DOMDocument $document
         */
        class XmlNamedNodeList extends ReadonlyCollection {

            /**
             * Документ
             *
             * @var \DOMDocument
             */
            private $_document;
    
            /**
             * Конструктор
             *
             * @param \DOMNodeList $nodelist Список узлов
             * @param \DOMDocument $dom Документ
             */
            public function __construct(\DOMNodeList $nodelist, \DOMDocument $dom) {
                $this->_document = $dom;
    
                $data = array();
                foreach($nodelist as $node) {
                    $data[$node->nodeName] = $node;
                }
    
                parent::__construct($data);
            }

            /**
             * Возвращает итератор для обхода методом foreach
             *
             * @return XmlNamedNodeListIterator
             */
            public function getIterator() {
                return new XmlNamedNodeListIterator($this);
            }
            
            /**
             * Возвраащет узел по ключу
             *  
             * @param string $key
             * @return XmlNode
             */
            public function Item($key) {
                $v = parent::Item($key);
                if(is_null($v)) {
                    return null;
                }
                return new XmlNode($v, $this->_document);
            }
    
            /**
             * Возвращает узел по индексу
             *
             * @param int $index
             * @return XmlNode
             */
            public function ItemAt($index) {
                return new XmlNode(parent::ItemAt($index), $this->_document);
            }
            
            /**
             * Геттер
             *
             * @param string $property
             * @return mixed
             */
            public function __get($property) {
                if(strtolower($property) == 'document') {
                    return $this->_document;
                }
                else {
                    return parent::__get($property);
                }
            }
    
        }

    }