<?php
    /**
     * Serialization
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2020 ColibriLab
     * @package Colibri\Xml\Serialization
     *
     */
    namespace Colibri\Xml\Serialization {

        use Colibri\Helpers\Variable;

        /**
         * Представляет собой десериализованный из xml обьект
         * @property string $name
         * @property array $attributes - аттрибуты
         * @property mixed $content - данные
         */
        class XmlSerialized implements \JsonSerializable {

            /**
             * Название элемента
             *
             * @var string
             */
            private $_name;

            /**
             * Список атрибутов
             *
             * @var stdCLass
             */
            private $_attributes;

            /**
             * Список элементов
             *
             * @var stdClass|array
             */
            private $_content;

            /**
             * Конструктор
             *
             * @param string $name название элемента
             * @param array $attributes список атрибутов
             * @param array $content контент
             */
            public function __construct($name = null, $attributes = null, $content = null) {
                $this->_name = $name;
                $this->_attributes = (object)$attributes;
                $this->_content = $content;
            }

            /**
             * Геттер
             *
             * @param string $property
             * @return mixed
             */
            public function __get($property) {
                if(strtolower($property) == 'attributes') {
                    return $this->_attributes;
                }
                else if(strtolower($property) == 'content') {
                    return $this->_content;
                }
                else if(strtolower($property) == 'name') {
                    return $this->_name;
                }
            }

            /**
             * Сеттер
             *
             * @param string $property
             * @param mixed $value
             */
            public function __set($property, $value) {
                if(strtolower($property) == 'attributes') {
                    $this->_attributes = (object)$value;
                }
                else if(strtolower($property) == 'content') {
                    $this->_content = $value;
                }
                else if(strtolower($property) == 'name') {
                    $this->_name = $value;
                }
                else {
                    if(!is_array($this->_content)) {
                        $this->_content = array();
                    }
                    $this->_content[$property] = $value;
                }
            }

            /**
             * Возвращает обьект для последующей сериализации в json
             *
             * @return stdClass
             */
            public function jsonSerialize()
            {
                return (object)array('class' => self::class,'name' => $this->_name, 'content' => $this->_content, 'attributes' => $this->_attributes);
            }

            /**
             * Поднимает обьект из json
             *
             * @param string $jsonString строка в которую запакован обьект XmlSeralized
             * @return XmlSerialized
             */
            public static function jsonUnserialize($jsonString) {
                $object = is_string($jsonString) ? json_decode($jsonString, true) : $jsonString;
                if(is_null($object)) {
                    return null;
                }

                if(isset($object['class'])) {
                    // если это мой обьект
                    $className = $object['class'];
                    if($className == 'XmlCData') {
                        return new XmlCData($object['value']);
                    }
                    else {
                        $class = new $className;
                        foreach ($object as $key => $value) {
                            if ($key !== 'class') {
                                $class->$key = XmlSerialized::jsonUnserialize(json_encode($value));
                            }
                        }
                        return $class;
                    }
                }
                else if(!is_array($object)) {
                    return $object;
                }
                else if(Variable::IsAssociativeArray($object)) {
                    $ret = [];
                    foreach($object as $key => $value) {
                        $ret[$key] = XmlSerialized::jsonUnserialize(json_encode($value));
                    }
                    return $ret;
                }
                else if(is_array($object)) {
                    $ret = [];
                    foreach($object as $value) {
                        $ret[] = XmlSerialized::jsonUnserialize(json_encode($value));
                    }
                    return $ret;
                }
                return $object;
            }
            
        }

    }