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

        use ArrayAccess;
        use Colibri\Events\TEventDispatcher;
        use Colibri\Helpers\Variable;
        use InvalidArgumentException;
        use IteratorAggregate;
    use Traversable;

/**
         * Класс обьект, все обьектноподобные классы и обьекты будут наследоваться от него
         */
        class ExtendedObject implements ArrayAccess, IteratorAggregate
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
                    if ($data instanceof ExtendedObject) {
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
            public function SetData($data)
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
            public function Original()
            {
                return (object)$this->_original;
            }

            /**
             * Возвращает префикс полей обьекта
             *
             * @return string
             */
            public function Prefix()
            {
                return $this->_prefix;
            }

            /**
             * Возвращает текущие данные обьекта (без изменений)
             *
             * @return \stdClass
             */
            public function GetData()
            {
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

            /**
             * Проверяет на наличие свойства в обьекте
             *
             * @param string $name название свойства
             * @return boolean
             */
            public function __isset($name)
            {
                if (!$this->$name) {
                    return false;
                }
                return true;
            }

            /**
             * Удаляет свойство из обьекта
             *
             * @param string $name название свойства
             */
            public function __unset($name)
            {
                unset($this->_data[$name]);
            }

            /**
             * Магическая функция
             *
             * @param string $property название свойства
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
             * @param string $property название свойства
             * @param mixed $value значение свойства
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
             * Обработчик по умолчанию события TypeExchange для замены типов
             *
             * @param string $mode - Режим 'get' или 'set'
             * @param string $property - название свойства
             * @param mixed $value значение свойства
             */
            protected function _typeExchange($mode, $property, $value = null)
            {
                if ($mode == 'get') {
                    return $this->_data[$property];
                } else {
                    $this->_data[$property] = $value;
                }
            }
            
            /**
             * Возвращает итератор
             * @return ExtendedObjectIterator 
             */
            public function getIterator()
            {
                return new ExtendedObjectIterator($this->GetData());
            }

            /**
             * Устанавливает значение по индексу
             * @param string $offset
             * @param DataRow $value
             * @return void
             */
            public function offsetSet($offset, $value)
            {
                if (!Variable::IsString($offset)) {
                    throw new InvalidArgumentException();
                }
                $this->$offset = $value;
            }
        
            /**
             * Проверяет есть ли данные по индексу
             * @param string $offset
             * @return bool
             */
            public function offsetExists($offset)
            {
                if (!Variable::IsString($offset)) {
                    throw new InvalidArgumentException();
                }
                return isset($this->$offset);
            }
        
            /**
             * удаляет данные по индексу
             * @param string $offset
             * @return void
             */
            public function offsetUnset($offset)
            {
                if (!Variable::IsString($offset)) {
                    throw new InvalidArgumentException();
                }
                unset($this->$offset);
            }
        
            /**
             * Возвращает значение по индексу
             *
             * @param string $offset
             * @return mixed
             */
            public function offsetGet($offset)
            {
                if (!Variable::IsString($offset)) {
                    throw new InvalidArgumentException();
                }
                return $this->$offset;
            }
        }
    }
