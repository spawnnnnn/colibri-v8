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
         * Базовый класс списка, реализует стандартный функционал
         */
        class ArrayList implements IArrayList
        {
        
            /**
             * Данные
             *
             * @var mixed
             */
            protected $data = null;
        
            /**
             * Конструктор
             * Создаем ArrayList из массива или объекта или из другого ArrayList-а
             *
             * @param mixed $data
             */
            public function __construct($data = array())
            {
                if (is_array($data)) {
                    $this->data = $data;
                } elseif (is_object($data) && $data instanceof IArrayList) {
                    $this->data = $data->ToArray();
                }

                if (is_null($this->data)) {
                    $this->data = array();
                }
            }
        
            /**
             * Получить иттератор
             *
             * @return ArrayListIterator
             */
            public function getIterator()
            {
                return new ArrayListIterator($this);
            }
        
            /**
             * Проверка наличия значения в списке
             *
             * @param mixed $item
             * @return boolean
             */
            public function Contains($item)
            {
                return in_array($item, $this->data, true);
            }
        
            /**
             * Возвращает индекс по значению
             *
             * @param mixed $item
             * @return integer
             */
            public function IndexOf($item)
            {
                return array_search($item, $this->data, true);
            }
        
            /**
             * Возвращает значение по идексу
             *
             * @param integer $index
             * @return mixed
             */
            public function Item($index)
            {
                return $this->data[$index];
            }
        
            /**
             * Добавляет значение с список
             *
             * @param mixed $value
             * @return mixed
             */
            public function Add($value)
            {
                $this->data[] = $value;
                return $value;
            }
        
            /**
             * Устанавливает значение по указанному индексу
             *
             * @param integer $index
             * @param mixed $value
             * @return mixed
             */
            public function Set($index, $value)
            {
                $this->data[$index] = $value;
                return $value;
            }
        
            /**
             * Добавляет значения
             *
             * @param mixed $values
             * @return void
             */
            public function Append($values)
            {
                if ($values instanceof IArrayList) {
                    $values = $values->ToArray();
                }
                
                $this->data = array_merge($this->data, $values);
            }

            /**
             * Внедряет значение в список после индекса
             *
             * @param mixed $value
             * @param integer $toIndex
             * @return void
             */
            public function InsertAt($value, $toIndex)
            {
                array_splice($this->data, $toIndex, 0, array($value));
            }
        
            /**
             * Удаляет значение
             *
             * @param mixed $value
             * @return boolean|mixed
             */
            public function Delete($value)
            {
                $indices = array_search($value, $this->data, true);
                if ($indices && count($indices) > 0) {
                    return array_splice($this->data, $indices[0], 1);
                }
                return false;
            }

            /**
             * Удаляет значение по индексу
             *
             * @param integer $index
             * @return mixed
             */
            public function DeleteAt($index)
            {
                return array_splice($this->data, $index, 1);
            }
        
            /**
             * Очищает список
             *
             * @return void
             */
            public function Clear()
            {
                $this->data = array();
            }

            /**
             * В строку
             *
             * @param string $splitter
             */
            public function ToString($splitter = ',')
            {
                return implode($splitter, $this->data);
            }
        
            /**
             * Возвращает массив
             */
            public function ToArray()
            {
                return $this->data;
            }

            /**
             * Возвращает количество
             *
             * @return int
             */
            public function Count()
            {
                return count($this->data);
            }

            /**
             * Возвращает первый элемент
             *
             * @return mixed
             */
            public function First()
            {
                return $this->Item(0);
            }

            /**
             * Возвращает последний элемент
             *
             * @return mixed
             */
            public function Last()
            {
                return $this->Item($this->Count()-1);
            }

            /**
             * Соединяет значения в строку с делимитером
             *
             * @param string $delimiter разделитель
             */
            public function Join($delimiter = ',')
            {
                $ret = '';
                foreach ($this as $item) {
                    $it = $item;
                    if (is_object($it) && method_exists($it, "Join")) {
                        $it = $it->Join($delimiter);
                    }
                    $ret .= $delimiter.$it;
                }
                return substr($ret, strlen($delimiter));
            }

            /**
             * Сортирует данные в массиве
             *
             * @param string $k - параметр внутри данных значения по которому нужно сортировать
             * @param mixed $sorttype - порядок сортировки
             */
            public function Sort($k, $sorttype = SORT_ASC)
            {
                $rows = array();
                $i = 0;
                foreach ($this->data as $row) {
                    if (is_object($row)) {
                        $key = $row->$k;
                    } else {
                        $key = $row[$k];
                    }

                    if (isset($rows[$key])) {
                        $key = $key.($i++);
                    }
                    $rows[$key] = $row;
                }

                if ($sorttype == SORT_ASC) {
                    ksort($rows);
                } else {
                    krsort($rows);
                }
                $this->data = array_values($rows);
            }

            /**
             * Вырезает кусок из массива и возвращает в виде ArrayList
             *
             * @param int $start - начало
             * @param int $count - количество
             * @return ArrayList - вырезанная часть массива
             */
            public function Splice($start, $count)
            {
                $part = array_splice($this->data, $start, $count);
                return new ArrayList($part);
            }

            /**
             * @param int $offset
             * @param mixed $value
             * @return void
             */
            public function offsetSet($offset, $value)
            {
                if (is_null($offset)) {
                    $this->Add($value);
                } else {
                    $this->Set($offset, $value);
                }
            }
        
            /**
             * @param int $offset
             * @return bool
             */
            public function offsetExists($offset)
            {
                return $offset < $this->Count();
            }
        
            /**
             * @param int $offset
             * @return void
             */
            public function offsetUnset($offset)
            {
                $this->DeleteAt($offset);
            }
        
            /**
             * Возвращает значение по индексу
             *
             * @param int $offset
             * @return mixed
             */
            public function offsetGet($offset)
            {
                return $this->Item($offset);
            }
            
        }
    }
