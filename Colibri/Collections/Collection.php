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
        class Collection extends ReadonlyCollection implements ICollection
        {

            
            /**
             * Добавляет ключ значение в коллекцию, если ключ существует
             * будет произведена замена
             *
             * @param string $key
             * @param mixed $value
             */
            public function Add($key, $value)
            {
                $this->data[strtolower($key)] = $value;
                return $value;
            }

            /**
             * Добавляет значения из другой коллекции, массива или обьекта
             * Для удаления необходимо передать свойство со значением null
             *
             * @param mixed $from - коллекция | массив
             */
            public function Append($from)
            {
                foreach ($from as $key => $value) {
                    if (is_null($value)) {
                        $this->Delete($key);
                    } else {
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
            public function Insert($index, $key, $value)
            {
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
            public function Delete($key)
            {
                $key = strtolower($key);
                if (array_key_exists($key, $this->data)) {
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
            public function DeleteAt($index)
            {
                $key = $this->Key($index);
                if ($key !== null) {
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
            public function Clear()
            {
                $this->data = array();
            }

            /**
             * Обход всей коллекции с изменением данных
             * @param {callable(string, mixed): mixed} $mapFunction
             * @return Collection
             */
            public function Map($mapFunction) {
                return new Collection(parent::Map($mapFunction)); 
            }
            
            /**
             * Магическая функция
             *
             * @param string $key
             * @param mixed $value
             * @return void
             */
            public function __set($key, $value)
            {
                $this->Add($key, $value);
            }

            /**
             * Устанавливает значение по индексу
             * @param string $offset
             * @param mixed $value
             * @return void
             */
            public function offsetSet($offset, $value)
            {
                $this->Add($offset, $value);
            }

            /**
             * Удаляет значение по индексу
             * @param mixed $offset
             * @return void
             */
            public function offsetUnset($offset)
            {
                if (is_numeric($offset)) {
                    $this->DeleteAt($offset);
                } else {
                    $this->Delete($offset);
                }
            }

        }
        
    }
