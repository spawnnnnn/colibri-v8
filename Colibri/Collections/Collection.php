<?php

    /**
     * Collections
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2020 ColibriLab
     * @package Colibri\Collections
     * 
     */
    namespace Colibri\Collections {

        /**
         * Базовый класс коллекций
         */
        class Collection implements ICollection {

            /**
             * Данные коллекции
             *
             * @var mixed
             */
            protected $data = null;

            /**
             * Конструктор, передается массив или обькет, или другая
             * коллекция для инициализации
             * Инициализация с помощью: array, stdClass, и любого другого ICollection
             * 
             * @param mixed $data
             */
            public function __construct($data = array()) {
                if(is_array($data)) {
                    $this->data = $data;
                }
                else if(is_object($data)) {
                    $this->data = $data instanceof ICollection ? $data->ToArray() : (array)$data;
                }

                if(is_null($this->data)) {
                    $this->data = array();
                }

                $this->data = array_change_key_case($this->data, CASE_LOWER);

            }

            /**
             * Проверяет наличие ключа
             *
             * @param string $key - ключ для проверки
             */
            public function Exists($key) {
                return array_key_exists($key, $this->data);
            }

            /**
             * Проверяет содержит ли коллекция значение
             *
             * @param mixed $item - значение для проверки
             */
            public function Contains($item){
                return in_array($item, $this->data, true);
            }

            /**
             * Находит значение и возвращает индекс
             *
             * @param mixed $item - значение для поиска
             */
            public function IndexOf($item) {
                $return = array_search($item, array_values($this->data), true);
                if($return === false) {
                    return null;
                }
                return $return;
            }

            /**
             * Возвращает ключ по индексу
             *
             * @param mixed $index
             */
            public function Key($index) {
                if($index >= $this->Count()) {
                    return null;
                }

                $keys = array_keys($this->data);
                if(count($keys) > 0) {
                    return $keys[$index];
                }

                return null;
            }

            /**
             * Возвращает значение по ключу
             *
             * @param mixed $key
             */
            public function Item($key) {
                if($this->Exists($key)) {
                    return $this->data[$key];
                }
                return null;
            }

            /**
             * Возвращает знаение по индексу
             *
             * @param mixed $index
             */
            public function ItemAt($index) {
                $key = $this->Key($index);
                if(!$key) {
                    return null;
                }
                return $this->data[$key];
            }

            /**
             * Возвращает итератор
             *
             */
            public function getIterator() {
                return new CollectionIterator($this);
            }

            /**
             * Добавляет ключ значение в коллекцию, если ключ существует
             * будет произведена замена
             *
             * @param string $key
             * @param mixed $value
             */
            public function Add($key, $value) {
                $this->data[strtolower($key)] = $value;
                return $value;
            }

            /**
             * Добавляет значения из другой коллекции, массива или обьекта
             * Для удаления необходимо передать свойство со значением null
             *
             * @param mixed $from - коллекция | массив
             */
            public function Append($from) {
                foreach($from as $key => $value) {
                    if(is_null($value)) {
                        $this->Delete($key);
                    }
                    else {
                        $this->Add($key, $value);
                    }
                }
            }

            /**
             * Добавляет значение в указанное место в коллекцию
             *
             * @param mixed $index
             * @param mixed $key
             * @param mixed $value
             */
            public function Insert($index, $key, $value) {
                $before = array_splice($this->data, 0, $index);
                $this->data = array_merge(
                                $before,
                                array($key => $value),
                                $this->data
                );
                return $value;
            }

            /**
             * Удаляет значение по ключу
             *
             * @param string $key
             * @return boolean
             */
            public function Delete($key) {
                $key = strtolower($key);
                if(array_key_exists($key, $this->data)) {
                    unset($this->data[$key]);
                    return true;
                }
                return false;
            }

            /**
             * Удаляет значение по индексу
             *
             * @param int $index
             * @return boolean
             */
            public function DeleteAt($index) {
                $key = $this->Key($index);
                if($key !== null) {
                    $this->Delete($key);
                    return true;
                }
                return false;
            }

            /**
             * Очищает коллекцию
             *
             * @return void
             */
            public function Clear() {
                $this->data = array();
            }

            /**
             * Превращает в строку
             *
             * @param string[] $splitters
             * @param mixed $mapFunction
             * @return string
             */
            public function ToString($splitters = null, $mapFunction = false) {
                $ret = '';
                foreach($this->data as $k => $v) {
                    if(!$mapFunction) {
                        $ret .= $splitters[1].$k.$splitters[0].$v;
                    }
                    else {
                        $ret .= $splitters[1].$k.$splitters[0].$mapFunction($v);
                    }
                }
                return substr($ret, 1);
            }

            /**
             * Парсит строку и сохраняет в коллекцию
             *
             * @param string $string
             * @param string[] $splitters
             * @return Collection
             */
            public static function FromString($string, $splitters = null) {
                $ret = array();
                $parts = explode($splitters[1], $string);
                foreach($parts as $part) {
                    $part = explode($splitters[0], $part);
                    $ret[$part[0]] = $part[1];
                }
                return new Collection($ret);
            }

            /**
             * Возвращает данные в виде массива
             *
             * @return array
             */
            public function ToArray() {
                return $this->data;
            }

            /**
             * Количество значений в коллекции
             *
             * @return void
             */
            public function Count() {
                return count($this->data);
            }

            /**
             * Возвращает первое значение
             *
             * @return mixed
             */
            public function First() {
                return $this->ItemAt(0);
            }

            /**
             * Возвращает последнее значение
             *
             * @return mixed
             */
            public function Last() {
                return $this->ItemAt($this->Count()-1);
            }

            /**
             * Магическая функция
             *
             * @param string $property
             * @return mixed
             */
            public function __get($property) {
                return $this->Item(strtolower($property));
            }

            /**
             * Магическая функция
             *
             * @param string $property
             * @param mixed $value
             * @return void
             */
            public function __set($key, $value) {
                $this->Add($key, $value);
            }

            /**
             * @param string $offset
             * @param mixed $value
             * @return void
             */
            public function offsetSet($offset, $value) {
                $this->Add($offset, $value);
            }
        
            /**
             * @param mixed $offset
             * @return bool
             */
            public function offsetExists($offset) {
                if(is_numeric($offset)) {
                    return $this->ItemAt($offset) !== null;
                }
                else {
                    return $this->Item($offset) !== null;
                }
            }
        
            /**
             * @param mixed $offset
             * @return void
             */
            public function offsetUnset($offset) {
                if(is_numeric($offset)) {
                    $this->DeleteAt($offset);
                }
                else {
                    $this->Delete($offset);
                }
            }
        
            /**
             * Возвращает значение по индексу
             *
             * @param mixed $offset
             * @return mixed
             */
            public function offsetGet($offset) {
                if(is_numeric($offset)) {
                    return $this->ItemAt($offset);
                }
                else {
                    return $this->Item($offset);
                }
            }

        }
        
    }