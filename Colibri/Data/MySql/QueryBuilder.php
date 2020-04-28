<?php

    /**
     * Драйвер для MySql
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Utils\Config
     * 
     *
     */
    namespace Colibri\Data\MySql {
        
        use Colibri\Data\SqlClient\IQueryBuilder;

        /**
         * Класс генератор запросов для драйвераMySql
         */
        class QueryBuilder implements IQueryBuilder
        {
            /**
             * Создает запрос ввода данных
             *
             * @param string $table
             * @param stdClass $data
             * @param string $returning
             * @return string
             */
            public function CreateInsert($table, $data, $returning = '')
            {
                $data = (array)$data;
                foreach ($data as $key => $value) {
                    if (is_null($value)) {
                        $value = 'null';
                    } else {
                        $value = '\''.addslashes($value).'\'';
                    }
                    $data[$key] = $value;
                }
            
                $keys = array_keys($data);
                $fields = '(`'.join("`, `", $keys).'`)';
            
                $vals = array_values($data);
                $values = "(".join(", ", $vals).")";
            
                return "insert into ".$table.$fields.' values'.$values;
            }
        
            /**
             * Создает запрос ввода данных или обновления в случае дублирования данных в индексных полях
             *
             * @param string $table
             * @param stdClass $data
             * @param array $exceptFields
             * @param string $returning
             * @return string
             */
            public function CreateInsertOrUpdate($table, $data, $exceptFields = array(), $returning = '')
            {
                $data = (array)$data;
                $keys = array_keys($data);
                $fields = '(`'.join("`, `", $keys).'`)';
            
                $vals = array_values($data);
                $values = "('".join("', '", $vals)."')";

                $updateStatement = '';
                foreach ($data as $k => $v) {
                    if (!in_array($k, $exceptFields)) {
                        $updateStatement .= ',`'.$k.'`=\''.addslashes($v).'\'';
                    }
                }

                return "insert into ".$table.$fields.' values '.$values.' on duplicate key update '.substr($updateStatement, 1);
            }
        
            /**
             * Создает запрос ввода данных пачкой
             *
             * @param string $table
             * @param array $data
             * @return string
             */
            public function CreateBatchInsert($table, $data)
            {
                $keys = array_keys($data[0]);
                $fields = '(`'.join("`, `", $keys).'`)';
            
                $values = '';
                foreach ($data as $row) {
                    $row = (array)$row;
                    $vals = array_values($row);
                    $values .= ",('".join("', '", $vals)."')";
                }
                $values = substr($values, 1);
            
                return "insert into ".$table.$fields.' values'.$values;
            }
        
            /**
             * Создает запрос на обновление данных
             *
             * @param string $table
             * @param string $condition
             * @param stdClass $data
             * @return string
             */
            public function CreateUpdate($table, $condition, $data)
            {
                $data = (array)$data;
                $q = '';
                foreach ($data as $k=>$v) {
                    $q .= ',`'.$k.'`='.(is_null($v) ? 'null' : '\''.addslashes($v).'\'');
                }
                return "update ".$table.' set '.substr($q, 1).' where '.$condition;
            }
        
            /**
             * Создает запрос на удаление данных
             *
             * @param string $table
             * @param string $condition
             * @return string
             */
            public function CreateDelete($table, $condition)
            {
                if (!empty($condition)) {
                    $condition = ' where '.$condition;
                }
                return (empty($condition) ? 'truncate table ' : 'delete from ').$table.$condition;
            }
        
            /**
             * Создает запрос на получение списка таблиц
             *
             * @return string
             */
            public function CreateShowTables()
            {
                return "show tables";
            }
        
            /**
             * Создает запрос на получение списка полей в таблице
             *
             * @param string $table
             * @return string
             */
            public function CreateShowField($table)
            {
                return "show columns from ".$table;
            }

        }
    }
?>
