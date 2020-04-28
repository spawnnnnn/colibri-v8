<?php

    namespace Colibri\Xml\Definitions {

        use Colibri\Xml\XmlNode;

        /**
         * Схема
         * 
         * @property-read [XsdSimpleTypeDefinition] $types
         * @property-read [XsdElementDefinition] $elements
         */
        class XsdSchemaDefinition implements \JsonSerializable {

            private $_schema;

            private $_types;

            public function __construct($fileName, $isFile = true) {
                $this->_schema = XmlNode::Load($fileName, $isFile);
                $this->_loadComplexTypes();
            }

            public static function Load($fileName, $isFile = true) {
                return new XsdSchemaDefinition($fileName, $isFile);
            }

            private function _loadComplexTypes() {
                $this->_types = [];
                $types = $this->_schema->Query('//xs:simpleType[@name]');
                foreach($types as $type) {
                    $t = new XsdSimpleTypeDefinition($type);
                    $this->_types[$t->name] = $t;
                }

                $types = $this->_schema->Query('//xs:complexType[@name]');
                foreach($types as $type) {
                    $t = new XsdSimpleTypeDefinition($type);
                    $this->_types[$t->name] = $t;
                }
            }

            public function __get($property) {
                if(strtolower($property) == 'types') {
                    return $this->_types;
                }
                else if(strtolower($property) == 'elements') {
                    $elements = [];
                    foreach($this->_schema->Query('./xs:element') as $element) {
                        $el = new XsdElementDefinition($element, $this);
                        $elements[$el->name] = $el;
                    }
                    return $elements;
                }
                return null;
            }

            public function jsonSerialize()
            {
                return (object)array('types' => $this->types, 'elements' => $this->elements);
            }

            public function ToObject() {

                $types = [];
                foreach($this->types as $type) {
                    $types[$type->name] = $type->ToObject();
                }

                $elements = [];
                foreach($this->elements as $element) {
                    $elements[$element->name] = $element->ToObject();
                }

                return (object)array('types' => $types, 'elements' => $elements);
            }
            

        }
    }