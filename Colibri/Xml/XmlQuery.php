<?php

    namespace Colibri\Xml {

        class XmlQuery {

            private $_contextNode;
            private $_operator;
            private $_returnAsNamedMap;
    
            public function __construct(XmlNode $node, $returnAsNamedMap = false) {
                $this->_returnAsNamedMap = $returnAsNamedMap;
                $this->_contextNode = $node;
                $this->_operator = new \DOMXPath($this->_contextNode->document);
            }
    
            /**
             * Выполняет запрос
             *
             * @param string $xpathQuery
             * @return XmlNodeList
             */
            public function Query($xpathQuery) {
                $res = $this->_operator->query($xpathQuery, $this->_contextNode->raw);
                if(!$res){
                    return new XmlNamedNodeList(new \DOMNodeList(), $this->_contextNode->document);
                }
                if($this->_returnAsNamedMap){
                    return new XmlNamedNodeList($res, $this->_contextNode->document);
                }
                return new XmlNodeList($res, $this->_contextNode->document);
            }
    
    
    
        }

    }