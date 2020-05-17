<?php
    /**
     * Definitions
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2020 ColibriLab
     * @package Colibri\Xml\Definitions
     *
     */
    namespace Colibri\Xml\Definitions {

        /**
         * Тип данных
         * 
         * @property-read string $name название типа
         * @property-read string $annotation аннотация типа
         * @property-read stdClass $restrictions ограничения
         */
        class XsdSimpleTypeDefinition implements \JsonSerializable {

            /**
             * Узел типа
             *
             * @var XmlNode
             */
            private $_node;

            /**
             * Конструктор
             *
             * @param XmlNode $typeNode
             */
            public function __construct($typeNode) {
                $this->_node = $typeNode;
            }

            /**
             * Геттер
             *
             * @param string $property
             * @return mixed
             */
            public function __get($property) {
                if(strtolower($property) == 'name') {
                    return $this->_node->attributes->name ? $this->_node->attributes->name->value : 'simpleType';
                }
                else if(strtolower($property) == 'annotation') {
                    $annotation = [];
                    $anno = $this->_node->Query('./xs:annotation');
                    foreach($anno as $a) {
                        $annotation[] = $a->value;
                    }
                    return trim(implode('', $annotation), "\n\r\t ");
                }
                else if(strtolower($property) == 'restrictions') {
                    $rest = $this->_node->Item('xs:restriction');
                    if(!$rest) {
                        return null;
                    }
                    $returnRestrictions = (object)['base' => str_replace('xs:', '', $rest->attributes->base ? $rest->attributes->base->value : null)];
                    $restrictions = $rest->children;
                    foreach($restrictions as $restriction) {
                        switch($restriction->name) {
                            case 'xs:enumeration': {
                                $ret = [];
                                foreach($this->_node->Item('xs:restriction')->Query('./*') as $enum) {
                                    $ret[$enum->attributes->value->value] = $enum->attributes->title ? $enum->attributes->title->value : $enum->attributes->value->value;
                                }
                                $returnRestrictions->enumeration = $ret;
                                break;
                            }
                            case 'xs:pattern': {
                                $returnRestrictions->pattern = $restriction->attributes->value->value;
                                break;
                            }
                            case 'xs:length': {
                                $returnRestrictions->length = $restriction->attributes->value->value;
                                break;
                            }
                            case 'xs:minLength': {
                                $returnRestrictions->minLength = $restriction->attributes->value->value;
                                break;
                            }
                            case 'xs:maxLength': {
                                $returnRestrictions->maxLength = $restriction->attributes->value->value;
                                break;
                            }
                            default: {
        
                            }
                        }
                    }
                    return $returnRestrictions;
                }
                else if(strtolower($property) == 'attributes') {
                    $attributes = [];
                    $attrs = $this->_node->Query('./xs:attribute');
                    if ($attrs->Count() > 0) {
                        foreach ($attrs as $attr) {
                            $a = new XsdAttributeDefinition($attr, $this->_schema);
                            $attributes[$a->name] = $a;
                        }
                    }
                    return $attributes;
                }
            }

            /**
             * Возвращает данные в виде простого обьекта для упаковки в json
             *
             * @return stdClass
             */
            public function jsonSerialize()
            {
                return (object)array('name' => $this->name, 'annotation' => $this->annotation, 'restrictions' => $this->restrictions, 'attributes' => $this->attributes);
            }

            /**
             * Возвращает данные в виде простого обьекта
             *
             * @return stdClass
             */
            public function ToObject()
            {
                return (object)array('name' => $this->name, 'annotation' => $this->annotation, 'restrictions' => $this->restrictions, 'attributes' => $this->attributes);
            }

        }
    }