<?php

    /**
     * Драйвер для MySql
     *
     * @author Ваган Григорян <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Utils\Config
     * @version 1.0.0
     *
     */
    namespace Colibri\Data\MySql {

    use Colibri\Data\SqlClient\DataField;
    use Colibri\Data\SqlClient\IDataReader;

        /**
         * Класс обеспечивающий работу с результатами запросов
         * 
         * @property-read bool $hasRows
         * @property-read int $affected
         * @property-read int $count
         * 
         */
        final class DataReader implements IDataReader
        {
            /**
             * Ресурс запроса
             *
             * @var resource
             */
            private $_results;

            /**
             * Количество результатов в текущей стрнице запроса
             *
             * @var int
             */
            private $_count = null;

            /**
             * Общее количество результатов
             * Заполнено только тогда когда запрос выполнен с параметром info=true в ExecuteReader
             *
             * @var int
             */
            private $_affected = null;
            
            /**
             * Создание обьекта
             *
             * @param resource $results
             * @param int $affected
             */
            public function __construct($results, $affected = null)
            {
                $this->_results = $results;
                $this->_affected = $affected;
            }
            
            /**
             * Закрытие ресурса обязательно
             */
            public function __destruct()
            {
                $this->Close();
            }
            
            /**
             * @inheritDoc
             */
            public function Close()
            {
                if ($this->_results) {
                    mysqli_free_result($this->_results);
                }
            }
            
            /**
             * @inheritDoc
             */
            public function Read()
            {
                $result = mysqli_fetch_object($this->_results);
                if (!$result) {
                    return false;
                }
                
                return $result;
            }

            /**
             * @inheritDoc
             */
            public function Fields()
            {
                
                $fields = array();
                $num = mysqli_num_fields($this->_results);
                for ($i=0; $i<$num; $i++) {
                    $f = mysqli_fetch_field_direct($this->_results, $i);
                    $field = new DataField();
                    $field->name = $f->name;
                    $field->originalName = $f->orgname;
                    $field->table = $f->table;
                    $field->originalTable = $f->orgtable;
                    $field->def = $f->def;
                    $field->maxLength = $f->max_length;
                    $field->length = $f->length;
                    $field->decimals = $f->decimals;
                    $field->type = $this->_type2txt($f->type);
                    $field->flags = $this->_flags2txt($f->flags);
                    $field->escaped = '`'.$field->originalTable.'`.`'.$field->originalName.'`';
                    $fields[$f->name] = $field;
                }
                return $fields;
            }

            /**
             * @inheritDoc
             */
            public function Count() {
                if (is_null($this->_count)) {
                    $this->_count = mysqli_num_rows($this->_results);
                }
                return $this->_count;
            }
            
            /**
             * @inheritDoc
             */
            public function Affected() {
                return $this->_affected;
            }

            /**
             * @inheritDoc
             */
            public function HasRows() {
                return $this->_results && mysqli_num_rows($this->_results) > 0;
            }


            private function _type2txt($type_id)
            {
                static $types;

                if (!isset($types))
                {
                    $types = array();
                    $constants = get_defined_constants(true);
                    foreach ($constants['mysqli'] as $c => $n) {
                        if (preg_match('/^MYSQLI_TYPE_(.*)/', $c, $m)) {
                            $types[$n] = $m[1];
                        }
                    }
                }

                return array_key_exists($type_id, $types)? $types[$type_id] : NULL;
            }

            private function _flags2txt($flags_num)
            {
                static $flags;

                if (!isset($flags))
                {
                    $flags = array();
                    $constants = get_defined_constants(true);
                    foreach ($constants['mysqli'] as $c => $n) { 
                        if (preg_match('/MYSQLI_(.*)_FLAG$/', $c, $m) && !array_key_exists($n, $flags)) { 
                            $flags[$n] = $m[1];
                        }
                    }
                }

                $result = array();
                foreach ($flags as $n => $t) { 
                    if ($flags_num & $n) {
                        $result[] = $t;
                    }
                }
                return $result;
            }

            
            
        }
    }
    
    
    
    
?>
