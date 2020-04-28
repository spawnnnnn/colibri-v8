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

        use ArrayAccess;

        /**
         * Интерфейс для именованных массивов
         */
        interface ICollection extends \IteratorAggregate, ArrayAccess
        {

            /**
             * Проверка существования ключа в массиве
             * @param string $key
             * @return boolean
             */
            public function Exists($key);
            
            /**
             * Вернуть ключ по индексу
             * @param int $index
             * @return string
             */
            public function Key($index);

            /**
             * Вернуть значение по ключу
             * @param string $key
             * @return mixed
             */
            public function Item($key);

            /**
             * Вернуть значение по индексу
             * @param int $index
             * @return mixed
             */
            public function ItemAt($index);

            /**
             * Добавить ключ значение, если ключ есть, то будет произведена замена
             * @param string $key
             * @param mixed $value
             * @return mixed
             */
            public function Add($key, $value);
            /**
             * Удалить ключ и значение из массива
             * @param string $key
             * @return boolean
             */
            public function Delete($key);

            /**
             * В строку, с соединителями
             * @param string[] $splitters
             * @return string
             */
            public function ToString($splitters = null);

            /**
             * вернуть данные в виде обычного массива
             * @return array
             */
            public function ToArray();

        }
        
    }
