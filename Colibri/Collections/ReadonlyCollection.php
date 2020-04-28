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
         * Коллекция без возможности записи
         */
        class ReadonlyCollection extends Collection {

            /**
             * Очистить
             *
             * @return void
             */
            public function Clean() {
                while(($index = $this->IndexOf('')) > -1) {
                    array_splice($this->data, $index, 1);
                }
            }
            
            /**
             * Блокирует добавление значений в коллекцию
             *
             * @param string $key
             * @param mixed $value
             * @return void
             * @throws CollectionException
             */
            public function Add($key, $value) { throw new CollectionException('This is a readonly collection'); }
            /**
             * Блокирует удаление значений в коллекцию
             *
             * @param string $key
             * @return void
             * @throws CollectionException
             */
            public function Delete($key) { throw new CollectionException('This is a readonly collection'); }
            
            
        }
        
    }