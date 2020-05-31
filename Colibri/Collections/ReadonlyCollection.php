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
         * Коллекция без возможности записи
         */
        class ReadonlyCollection implements IReadonlyCollection
        {

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
            public function __construct($data = array())
            {
                if (is_array($data)) {
                    $this->data = $data;
                } elseif (is_object($data)) {
                    $this->data = $data instanceof IReadonlyCollection ? $data->ToArray() : (array)$data;
                }

                if (is_null($this->data)) {
                    $this->data = array();
                }

                $this->data = array_change_key_case($this->data, CASE_LOWER);
            }

            /**
             * Проверяет наличие ключа
             *
             * @param string $key - ключ для проверки
             */
            public function Exists($key)
            {
                return array_key_exists($key, $this->data);
            }

            /**
             * Проверяет содержит ли коллекция значение
             *
             * @param mixed $item - значение для проверки
             */
            public function Contains($item)
            {
                return in_array($item, $this->data, true);
            }

            /**
             * Находит значение и возвращает индекс
             *
             * @param mixed $item - значение для поиска
             */
            public function IndexOf($item)
            {
                $return = array_search($item, array_values($this->data), true);
                if ($return === false) {
                    return null;
                }
                return $return;
            }

            /**
             * Возвращает ключ по индексу
             *
             * @param mixed $index
             */
            public function Key($index)
            {
                if ($index >= $this->Count()) {
                    return null;
                }

                $keys = array_keys($this->data);
                if (count($keys) > 0) {
                    return $keys[$index];
                }

                return null;
            }

            /**
             * Возвращает значение по ключу
             *
             * @param mixed $key
             */
            public function Item($key)
            {
                if ($this->Exists($key)) {
                    return $this->data[$key];
                }
                return null;
            }

            /**
             * Возвращает знаение по индексу
             *
             * @param mixed $index
             */
            public function ItemAt($index)
            {
                $key = $this->Key($index);
                if (!$key) {
                    return null;
                }
                return $this->data[$key];
            }

            /**
             * Возвращает итератор
             *
             */
            public function getIterator()
            {
                return new CollectionIterator($this);
            }

            /**
             * Обход всей коллекции
             * @param {callable(string, mixed): bool} $mapFunction
             * @return void
             */
            public function Each($mapFunction) {
                foreach($this as $key => $value) {
                    if($mapFunction($key, $value) === false) {
                        return;
                    }
                }  
            }

            /**
             * Обход всей коллекции с изменением данных
             * @param {callable(string, mixed): mixed} $mapFunction
             * @return ReadonlyCollection
             */
            public function Map($mapFunction) {
                $returning = [];
                foreach($this as $key => $value) {
                    $returning[$key] = $mapFunction($key, $value);
                }
                return new ReadonlyCollection($returning); 
            }

            /**
             * Превращает в строку
             *
             * @param string[] $splitters
             * @param {callable(mixed): string} $mapFunction
             * @return string
             */
            public function ToString($splitters = null, $mapFunction = null)
            {
                $ret = '';
                foreach ($this->data as $k => $v) {
                    if (!$mapFunction) {
                        $ret .= $splitters[1].$k.$splitters[0].$v;
                    } else {
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
            public static function FromString($string, $splitters = null)
            {
                $ret = array();
                $parts = explode($splitters[1], $string);
                foreach ($parts as $part) {
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
            public function ToArray()
            {
                return $this->data;
            }

            /**
             * Количество значений в коллекции
             *
             * @return void
             */
            public function Count()
            {
                return count($this->data);
            }

            /**
             * Возвращает первое значение
             *
             * @return mixed
             */
            public function First()
            {
                return $this->ItemAt(0);
            }

            /**
             * Возвращает последнее значение
             *
             * @return mixed
             */
            public function Last()
            {
                return $this->ItemAt($this->Count()-1);
            }

            /**
             * Магическая функция
             *
             * @param string $property
             * @return mixed
             */
            public function __get($property)
            {
                return $this->Item(strtolower($property));
            }

            /**
             * Устанавливает значение по индексу
             * @param string $offset
             * @param mixed $value
             * @return void
             */
            public function offsetSet($offset, $value)
            {
                throw new CollectionException('Can not modify readonly collection');
            }
        
            /**
             * Проверяет наличие значения по индексу
             * @param mixed $offset
             * @return bool
             */
            public function offsetExists($offset)
            {
                if (is_numeric($offset)) {
                    return $this->ItemAt($offset) !== null;
                } else {
                    return $this->Item($offset) !== null;
                }
            }
        
            /**
             * Удаляет значение по индексу
             * @param mixed $offset
             * @return void
             */
            public function offsetUnset($offset)
            {
                throw new CollectionException('Can not modify readonly collection');
            }
        
            /**
             * Возвращает значение по индексу
             *
             * @param mixed $offset
             * @return mixed
             */
            public function offsetGet($offset)
            {
                if (is_numeric($offset)) {
                    return $this->ItemAt($offset);
                } else {
                    return $this->Item($offset);
                }
            }

            
            
        }
        
    }
