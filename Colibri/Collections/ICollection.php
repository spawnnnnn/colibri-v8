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
         * Интерфейс для именованных массивов
         */
        interface ICollection extends IReadonlyCollection
        {

            /**
             * Добавить ключ значение, если ключ есть, то будет произведена замена
             *
             * @param string $key
             * @param mixed $value
             * @return mixed
             */
            public function Add($key, $value);

            /**
             * Добавляет значения из другой коллекции, массива или обьекта
             * Для удаления необходимо передать свойство со значением null
             *
             * @param mixed $from - коллекция | массив
             */
            public function Append($from);

            /**
             * Добавляет значение в указанное место в коллекцию
             *
             * @param mixed $index
             * @param mixed $key
             * @param mixed $value
             */
            public function Insert($index, $key, $value);

            /**
             * Удалить ключ и значение из массива
             *
             * @param string $key
             * @return boolean
             */
            public function Delete($key);

            /**
             * Удалить ключ и значение из массива по индексу
             *
             * @param string $index
             * @return boolean
             */
            public function DeleteAt($index);

            /**
             * Очистить
             *
             * @return void
             */
            public function Clear();

        }
        
    }
