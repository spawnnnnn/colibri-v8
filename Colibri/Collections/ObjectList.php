<?php

    namespace Colibri\Collections {

        /**
         * Список обьектов
         */
        class ObjectList extends ArrayList {

            
            /**
             * Найти обьект в списке по значению поля обьекта
             *
             * @param string $property
             * @param mixed $value
             * @return ObjectList
             */
            public function Find($property, $value) {
                $r = new ObjectList();
                foreach($this as $item) {
                    if($item->$property == $value) {
                        $r->Add($item);
                    }
                }
                return $r;
            }
            
        }
        
    }