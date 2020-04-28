<?php

    namespace Colibri\Xml\Definitions {

        /**
         * Представление атрибута
         * 
         * @property-read string $annotation
         * @property-read string $name
         * @property-read XsdSimplTypeDefinition|XsdBaseTypeDefinition $type
         * @property-read string $use
         * @property-read string $default
         */
        class XsdAttributeDefinition implements \JsonSerializable {
            
            private $_node;
            private $_schema;

            public function __construct($attributeNode, $schema)
            {
                $this->_node = $attributeNode;
                $this->_schema = $schema;
            }

            public function __get($name)
            {
                if(strtolower($name) == 'annotation') {
                    return $this->_node->Item('xs:annotation') ? trim($this->_node->Item('xs:annotation')->value, "\r\t\n ") : '';
                }
                else if(strtolower($name) == 'name') {
                    return $this->_node->attributes->name->value;
                }
                else if(strtolower($name) == 'type') {
                    if($this->_node->attributes->type) {
                        return isset($this->_schema->types[$this->_node->attributes->type->value]) ? $this->_schema->types[$this->_node->attributes->type->value] : new XsdBaseTypeDefinition($this->_node->attributes->type->value);
                    }
                    return new XsdSimpleTypeDefinition($this->_node->Item('xs:simpleType'));
                }
                else if(strtolower($name) == 'use') {
                    return $this->_node->attributes->use ? $this->_node->attributes->use->value : null;
                }
                else if(strtolower($name) == 'default') {
                    return $this->_node->attributes->default ? $this->_node->attributes->default->value : null;
                }
                else if(strtolower($name) == 'group') {
                    return $this->_node->attributes->group ? $this->_node->attributes->group->value : null;
                }
                else if(strtolower($name) == 'autocomplete') {
                    return $this->_node->attributes->autocomplete && $this->_node->attributes->autocomplete->value ? explode(',', $this->_node->attributes->autocomplete->value) : null;
                }
                else if(strtolower($name) == 'generate') {
                    return $this->_node->attributes->generate && $this->_node->attributes->generate->value ? $this->_node->attributes->generate->value : null;
                }
                else if(strtolower($name) == 'lookup') {
                    return $this->_node->attributes->lookup && $this->_node->attributes->lookup->value ? $this->_node->attributes->lookup->value : null;
                }
                
            }

            public function jsonSerialize()
            {
                return (object)array('name' => $this->name, 'annotation' => $this->annotation, 'type' => $this->type, 'use' => $this->use, 'default' => $this->default, 'group' => $this->group, 'autocomplete' => $this->autocomplete, 'generate' => $this->generate, 'lookup' => $this->lookup);
            }

            public function ToObject()
            {
                return (object)array('name' => $this->name, 'annotation' => $this->annotation, 'type' => $this->type->ToObject(), 'use' => $this->use, 'default' => $this->default, 'group' => $this->group, 'autocomplete' => $this->autocomplete, 'generate' => $this->generate, 'lookup' => $this->lookup);
            }

        }       
         
    }