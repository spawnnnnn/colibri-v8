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
        interface IReadonlyCollection extends \IteratorAggregate, \ArrayAccess {

            /**
             * Проверка существования ключа в массиве
             * 
             * @param string $key
             * @return boolean
             */
            public function Exists($key);

            /**
             * Проверяет содержит ли коллекция значение
             *
             * @param mixed $item - значение для проверки
             */
            public function Contains($item);
            
            /**
             * Вернуть ключ по индексу
             * 
             * @param int $index
             * @return string
             */
            public function Key($index);

            /**
             * Вернуть значение по ключу
             * 
             * @param string $key
             * @return mixed
             */
            public function Item($key);

            /**
             * Вернуть значение по индексу
             * 
             * @param int $index
             * @return mixed
             */
            public function ItemAt($index);

            /**
             * Находит значение и возвращает индекс
             *
             * @param mixed $item - значение для поиска
             */
            public function IndexOf($item);

            /**
             * В строку, с соединителями
             * 
             * @param string[] $splitters
             * @param {callable(mixed): bool} $mapFunction
             * @return string
             */
            public function ToString($splitters = null, $mapFunction = null);

            /**
             * вернуть данные в виде обычного массива
             * 
             * @return array
             */
            public function ToArray();

            /**
             * Парсит строку и сохраняет в коллекцию
             *
             * @param string $string
             * @param string[] $splitters
             * @return Collection
             */
            public static function FromString($string, $splitters = null);

            /**
             * Обход всей коллекции
             * @param {callable(string, mixed): bool} $mapFunction
             * @return void
             */
            public function Each($mapFunction);

            /**
             * Обход всей коллекции с изменением данных
             * @param {callable(string, mixed): bool} $mapFunction
             * @return IReadonlyCollection
             */
            public function Map($mapFunction);
            
            /**
             * Количество значений в коллекции
             *
             * @return void
             */
            public function Count();

            /**
             * Возвращает первое значение
             *
             * @return mixed
             */
            public function First();
            
            /**
             * Возвращает последнее значение
             *
             * @return mixed
             */
            public function Last();

        }

    }