<?php

    namespace Colibri\Xml {

        use Colibri\Collections\ObjectCollection;

        class XmlNamedNodeList extends ObjectCollection {

            /**
             * Документ
             *
             * @var \DOMDocument
             */
            private $_document;
    
            public function __construct(\DOMNodeList $nodelist, \DOMDocument $dom) {
                $this->_document = $dom;
    
                $data = array();
                foreach($nodelist as $node) {
                    $data[$node->nodeName] = $node;
                }
    
                parent::__construct($data);
            }
    
            public function Item($key) {
                $v = parent::Item($key);
                if(is_null($v)) {
                    return null;
                }
                return new XmlNode($v, $this->_document);
            }
    
            public function ItemAt($index) {
                return new XmlNode(parent::ItemAt($index), $this->_document);
            }
    
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