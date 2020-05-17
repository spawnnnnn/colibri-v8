<?php
    /**
     * MySql
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Data\MySql
     */
    namespace Colibri\Data\MySql {


        use Colibri\Data\SqlClient\NonQueryInfo as SqlNonQueryInfo;

        /**
         * Класс для хранения результатов запроса, если не требуется получение табличных данных
         *
         * @property string $type
         * @property int $insertid
         * @property int $affected
         * @property string $error
         * @property string $query
         *
         */
        final class NonQueryInfo extends SqlNonQueryInfo
        {
            /**
             * Конструктор
             *
             * @param string $type
             * @param int $insertid
             * @param int $affected
             * @param string $error
             * @param string $query
             */
            public function __construct($type, $insertid, $affected, $error, $query)
            {
                $this->type = $type;
                $this->insertid = $insertid;
                $this->affected = $affected;
                $this->error = $error;
                $this->query = $query;
            }
        }

    }
