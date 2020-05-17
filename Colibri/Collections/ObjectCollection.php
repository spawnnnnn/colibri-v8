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

        use Colibri\Helpers\Variable;

        /**
         * Коллекция обьектов без возможности записи
         */
        class ObjectCollection extends ReadonlyCollection
        {
            /**
             * Возвращаемый класс
             *
             * @var string
             */
            private $itemClass = '';
            
            /**
             * Конструктор
             * @param array $data
             * @param string $itemClass возвращаемый класс
             */
            public function __construct($data = array(), $itemClass = '')
            {
                parent::__construct($data);
                $this->itemClass = $itemClass;
            }
            
            /**
             * Вернуть обьект по ключу
             *
             * @param string $key
             * @return mixed
             */
            public function Item($key)
            {
                if (Variable::IsEmpty($this->itemClass)) {
                    return $this->data[$key];
                } else {
                    return new $this->itemClass($this->data[$key]);
                }
            }
            
            /**
             * Вернуть обьект по индексу
             *
             * @param integer $index
             * @return mixed
             */
            public function ItemAt($index)
            {
                $key = $this->Key($index);
                if (!$key) {
                    return false;
                }
                    
                if (Variable::IsEmpty($this->itemClass)) {
                    return $this->data[$key];
                } else {
                    return new $this->itemClass($this->data[$key]);
                }
            }
        }

    }
