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
         * Интерфейс списка
         */
        interface IArrayList extends \ArrayAccess, \IteratorAggregate
        {
            /**
             * Возвращает знаение по индексу
             *
             * @param integer $index
             * @return mixed
             */
            public function Item($index);
        
            /**
             * Добавляет значение в ArrayList
             * @param mixed $value
             * @return mixed
             */
            public function Add($value);
            /**
             * Добавляет список значений в массив
             *
             * @param mixed $values
             * @return mixed
             */
            public function Append($values);
        
            /**
             * Удаляет значение из ArrayList-а
             *
             * @param mixed $value
             * @return boolean
             */
            public function Delete($value);
            /**
             * Удаляет значение из ArrayList-а по индексу
             *
             * @param int $index
             * @return boolean
             */
            public function DeleteAt($index);
        
            /**
             * Превращает в строку
             * @param string $splitter
             * @return string
             */
            public function ToString($splitter = ',');
            /**
             * Возвращает массив из значений
             *
             * @return array
             */
            public function ToArray();
            
        }
    }