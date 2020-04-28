<?php
    /**
     * Узел в файле конфигурации
     * 
     * @author Ваган Григорян <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Utils\Config
     * @version 1.0.0
     * 
     */
    namespace Colibri\Configuration {

        use Colibri\Collections\ArrayList;

        /**
         * Узел список в файле конфигурации
         */
        class ConfigItemsList extends ArrayList {

            private $_type;

            public function __construct($data, string $type = '')
            {
                parent::__construct($data);
                $this->_type = $type;
            }

            /**
             * Возвращает значение по идексу
             *
             * @param integer $index
             * @return Config
             */
            public function Item($index)
            {
                return new Config($this->data[$index], $this->_type);
            }

        }
        

    }