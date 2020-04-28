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
         * Список обьектов
         */
        class ObjectList extends ArrayList
        {

            /**
             * Возвращаемый класс
             *
             * @var string
             */
            private $itemClass = '';
            
            /**
             * @param array $data
             * @param string $itemClass возвращаемый класс
             */
            public function __construct($data = array(), $itemClass = '')
            {
                parent::__construct($data);
                $this->itemClass = $itemClass;
            }
            
            /**
             * Найти обьект в списке по значению поля обьекта
             *
             * @param string $property
             * @param mixed $value
             * @return ObjectList
             */
            public function Find($property, $value)
            {
                $r = new ObjectList();
                foreach ($this as $item) {
                    if ($item->$property == $value) {
                        $r->Add($item);
                    }
                }
                return $r;
            }

            public function Item($index) {
                if (Variable::IsEmpty($this->itemClass)) {
                    return $this->data[$index];
                } else {
                    return new $this->itemClass($this->data[$index]);
                }
            }
        }
        
    }
