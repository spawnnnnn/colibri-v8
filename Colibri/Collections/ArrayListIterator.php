<?php

    /**
     * Итератор списка
     * 
     * @author Ваган Григорян <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Collections
     * @version 1.0.0
     * 
     */
    namespace Colibri\Collections {

        /**
         * Итератор списка
         */
        class ArrayListIterator implements \Iterator
        {
            /**
             * Список
             *
             * @var ArrayList
             */
            private $_class;

            /**
             * Текущая позиция
             *
             * @var integer
             */
            private $_current = 0;
        
            /**
             * Создает итератор для ArrayList-а
             *
             * @param IArrayList $class
             */
            public function __construct($class = null)
            {
                $this->_class = $class;
            }
        
            /**
             * Перескакивает на первое значение и возвращает позицию
             *
             * @return int
             */
            public function rewind()
            {
                $this->_current = 0;
                return $this->_current;
            }
        
            /**
             * Возвращает текущую позицию
             *
             * @return int
             */
            public function current()
            {
                if ($this->valid()) {
                    return $this->_class->Item($this->_current);
                } else {
                    return false;
                }
            }
        
            /**
             * Возвращает ключ текущей позиции
             *
             * @return string
             */
            public function key()
            {
                return $this->_current;
            }
        
            /**
             * Переходит дальше и возвращает значение
             *
             * @return mixed
             */
            public function next()
            {
                $this->_current++;
                if ($this->valid()) {
                    return $this->_class->Item($this->_current);
                } else {
                    return false;
                }
            }
        
            /**
             * Проверяет валидна ли текущая позиция
             *
             * @return bool
             */
            public function valid()
            {
                return $this->_current >= 0 && $this->_current < $this->_class->Count();
            }
        }
    }