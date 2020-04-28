<?php

    /**
     * Utils
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2020 ColibriLab
     * @package Colibri\Utils
     * 
     */
    namespace Colibri\Utils {

        use Colibri\Events\TEventDispatcher;

        /**
         * Класс обьект, все обьектноподобные классы и обьекты будут наследоваться от него
         *
         * @property string $prefix - Префикс полей
         * @property stdClass $data - Данные
         * @property stdClass $orifinal - Оригинальные данные перед изменением
         */
        class ObjectEx
        {

            use TEventDispatcher;

            /**
             * Содержит данные, которые получены при инициализации
             *
             * @var mixed
             */
            protected $_original;

            /**
             * Данные обьекта, свойства
             *
             * @var mixed
             */
            protected $_data;

            /**
             * Префикс свойств, для обеспечения правильной работы с хранилищами
             *
             * @var string
             */
            protected $_prefix = "";

            /**
             * Индикатор изменения обьекта
             *
             * @var boolean
             */
            protected $_changed = false;

            /**
             * Конструктор
             *
             * @param mixed $data - инициализационные данные
             * @param string $prefix - префикс
             */
            public function __construct($data = null, $prefix = "")
            {
                if (is_null($data)) {
                    $this->_data = array();
                } else {
                    if ($data instanceof ObjectEx) {
                        $this->_data = $data->ToArray();
                        $this->_prefix = $data->prefix;
                    } elseif (is_array($data)) {
                        $this->_data = $data;
                    } else {
                        $this->_data = get_object_vars($data);
                    }
                }

                if (!empty($prefix) && substr($prefix, strlen($prefix)-1, 1) != "_") {
                    $prefix = $prefix."_";
                }

                $this->_prefix = $prefix;
                $this->_original = $this->_data;
            }

            /**
             * Разрушает обьект
             *
             */
            public function __destruct()
            {
                unset($this->_data);
            }

            /**
             * Очищает все свойства
             *
             */
            public function Clear()
            {
                $this->_data = [];
            }

            /**
             * Установка данных в виде обьекта, полная замена
             *
             * @param mixed $data - данные
             */
            public function setData($data)
            {
                $this->_data = $data;
                $this->_changed = true;
            }

            /**
             * Возвращает ассоциативный массив, содержащий все данные
             *
             * @param boolean $noPrefix - удалить префиксы из свойств
             */
            public function ToArray($noPrefix = false)
            {
                $data = array();
                foreach ($this->_data as $key => $value) {
                    $value = $this->_typeExchange('get', $key);
                    if (is_array($value)) {
                        foreach ($value as $index => $v) {
                            if (method_exists($v, 'ToArray')) {
                                $value[$index] = $v->ToArray($noPrefix);
                            }
                        }
                    } elseif (method_exists($value, 'ToArray')) {
                        $value = $value->ToArray($noPrefix);
                    }
                    $data[!$noPrefix ? $key : substr($key, strlen($this->_prefix))] = $value;
                }
                return $data;
            }

            /**
             * Возвращает данные, которые были переданы при инициализации
             *
             * @return \stdClass
             */
            public function Original() {
                return (object)$this->_original;
            }

            /**
             * Возвращает префикс полей обьекта
             *
             * @return string
             */
            public function Prefix() {
                return $this->_prefix;
            }

            /**
             * Возвращает текущие данные обьекта (без изменений)
             *
             * @return \stdClass
             */
            public function GetData() {
                return $this->_data;
            }

            /**
             * Возвращает JSON строку данных обьекта
             *
             */
            public function ToJSON()
            {
                return json_encode($this->ToArray());
            }

            public function __isset($name)
            {
                if (!$this->$name) {
                    return false;
                }
                return true;
            }

            public function __unset($name)
            {
                unset($this->_data[$name]);
            }

            /**
             * Магическая функция
             *
             * @param string $property
             * @return mixed
             */
            public function __get($property)
            {
                if (!empty($this->_prefix) && strpos($property, $this->_prefix) === false) {
                    $property = $this->_prefix.$property;
                }
                return isset($this->_data[$property]) ? $this->_typeExchange('get', $property) : null;
            }

            /**
             * Магическая функция
             *
             * @param string $property
             * @param mixed $value
             */
            public function __set($property, $value)
            {
                if (!empty($this->_prefix) && strpos($property, $this->_prefix) === false) {
                    $property = $this->_prefix.$property;
                }
                $this->_typeExchange('set', $property, $value);            
                $this->_changed = true;

            }

            /**
             * Добавляет свойство к обьекту
             *
             * @param string $property
             * @param mixed $value
             */
            public function AddProperty($property, $value)
            {
                $property = $this->_prefix.$property;
                $this->_data[$property] = $value;
                $this->_changed = true;
            }
            
            /**
             * Возвращает свойство обьекта
             *
             * @param string $property
             */
            public function GetProperty($property)
            {
                $property = $this->_prefix.$property;
                return isset($this->_data[$property]) ? $this->_data[$property] : null;
            }

            /**
             * Удаляет свойство из обьекта
             *
             * @param mixed $property
             */
            public function DeleteProperty($property)
            {
                $property = $this->_prefix.$property;
                unset($this->_data[$property]);
                $this->_changed = true;
            }

            /**
             * Обработчик по умолчанию события TypeExchange для замены типов
             *
             * @param string $mode - Режим 'get' или 'set'
             * @param string $property - Свойство
             * @param mixed $value
             */
            protected function _typeExchange($mode, $property, $value = false)
            {
                if ($mode == 'get') {
                    return $this->_data[$property];
                } else {
                    $this->_data[$property] = $value;
                }
            }
        }
    }
