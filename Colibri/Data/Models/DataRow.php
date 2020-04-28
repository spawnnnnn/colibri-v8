<?php

    /**
     * Models
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Data\Models
     */
    namespace Colibri\Data\Models {

        use Colibri\Utils\ObjectEx;

        /**
         * Представление строки данных
         *
         * @property array $properties
         *
         */
        class DataRow extends ObjectEx
        {
            
            /**
             * Таблица
             *
             * @var DataTable
             */
            protected $_table;

            public function __construct(DataTable $table, $data = null, $tablePrefix = '')
            {
                parent::__construct($data, $tablePrefix);
                $this->_table = $table;
            }

            public static function Create()
            {
            }
            
            public function __get($property)
            {
                $return = null;
                $property = strtolower($property);
                if ($property == 'properties') {
                    $return = $this->_table->Fields();
                } else {
                    $return = parent::__get($property);
                }
                return $return;
            }
            
            public function __set($property, $value)
            {
                $property = strtolower($property);
                if ($property !== 'properties') {
                    parent::__set($property, $value);
                }
            }

            /**
             * Копирует в обьект
             *
             * @return ObjectEx
             */
            public function CopyToObject()
            {
                return new ObjectEx($this->_data, $this->_prefix);
            }
            
            public function Save()
            {
                if (!$this->_changed) {
                    return false;
                }

                $tables = [];
                $idFields = [];
                $fields = $this->properties;
                foreach ($fields as $field) {
                    if (in_array('PRI_KEY', $field->flags)) {
                        $idFields[] = $field->name;
                    }
                    $tables[$field->table] = $field->table;
                }

                if (count($tables) != 1) {
                    throw new DataModelException('Can not find any table name to use in save operation');
                }
                $table = reset($tables);

                if (count($idFields) == 0) {
                    throw new DataModelException('table does not have and autoincrement and can not be saved in standart mode');
                }

                $res = $this->_table->Point()->InsertOrUpdate($table, $this->_data, $idFields);
                if ($res->affected == 0) {
                    return false;
                }
                
                // если это ID то сохраняем
                if (count($idFields) == 1) {
                    foreach ($idFields as $f) {
                        $this->$f = $res->insertid;
                    }
                }

                return true;
            }
            
            public function Delete()
            {
                $tables = [];
                $idFields = [];
                $fields = $this->properties;
                foreach ($fields as $field) {
                    if (in_array('PRI_KEY', $field->flags)) {
                        $idFields[] = $field->name;
                    }
                    $tables[$field->table] = $field->table;
                }

                if (count($tables) != 1) {
                    throw new DataModelException('Can not find any table name to use in save operation');
                }
                $table = reset($tables);

                if (count($idFields) == 0) {
                    throw new DataModelException('table does not have and autoincrement and can not be saved in standart mode');
                }

                $condition = [];
                foreach ($idFields as $f) {
                    $condition[] = $f->escaped.'=\''.$this->{$f->name}.'\'';
                }

                $this->_table->Point()->Delete($table, implode(' and ', $condition));
            }
        }


    }
