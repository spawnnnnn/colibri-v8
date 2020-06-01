<?php

    /**
     * Models
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Data\Models
     */
    namespace Colibri\Data\Models {

        use Colibri\Utils\ExtendedObject;

        /**
         * Представление строки данных
         *
         * @property array $properties
         *
         */
        class DataRow extends ExtendedObject
        {
            
            /**
             * Таблица
             *
             * @var DataTable
             */
            protected $_table;

            /**
             * Конструктор
             *
             * @param DataTable $table
             * @param mixed $data
             * @param string $tablePrefix
             */
            public function __construct(DataTable $table, $data, $tablePrefix = '')
            {
                parent::__construct($data, $tablePrefix);
                $this->_table = $table;
            }
            
            /**
             * Геттер
             *
             * @param string $property
             * @return mixed
             */
            public function __get($property)
            {
                $return = null;
                $property = strtolower($property);
                if ($property == 'properties') {
                    $return = $this->_table->Fields();
                } elseif ($property == 'changed') {
                    $return = $this->_changed;
                } else {
                    $return = parent::__get($property);
                }
                return $return;
            }
            
            /**
             * Сеттер
             *
             * @param string $property
             * @param mixed $value
             */
            public function __set($property, $value)
            {
                $property = strtolower($property);
                if ($property == 'properties') {
                    throw new DataModelException('Can not set the readonly property');
                } elseif ($property == 'changed') {
                    $this->_changed = $value;
                } else {
                    parent::__set($property, $value);
                }
            }

            /**
             * Копирует в обьект
             *
             * @return ExtendedObject
             */
            public function CopyToObject()
            {
                return new ExtendedObject($this->_data, $this->_prefix);
            }
        }


    }
