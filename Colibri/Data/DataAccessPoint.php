<?php
    
    /**
     * Доступ к базе данных
     *
     * @author Ваган Григорян <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Utils\Config
     * @version 1.0.0
     *
     */
    namespace Colibri\Data {

        use Colibri\Data\SqlClient\IConnection;
        use Colibri\Data\SqlClient\Command;
        use Colibri\Data\SqlClient\IDataReader;
        use Colibri\Data\SqlClient\IQueryBuilder;
        use Colibri\Data\SqlClient\NonQueryInfo;
    use Colibri\Utils\Debug;

/**
         * Точка доступа
         *
         * @property-read string $name
         * @property-read IConnection $connection
         *
         */
        class DataAccessPoint
        {

            const QueryTypeReader = 'reader';
            const QueryTypeBigData = 'bigdata';
            const QueryTypeNonInfo = 'noninfo';
            
            /**
             * Информация о подключении
             *
             * @var stcClass
             */
            private $_accessPointData;

            /**
             * Подключение
             *
             * @var mixed
             */
            private $_connection;
            
            /**
             * Конструктор
             *
             * @param stdClass $accessPointData
             */
            public function __construct($accessPointData)
            {
                
                $this->_accessPointData = $accessPointData;
                
                // получаем название класса Connection-а, должен быть классом интерфейса IDataConnection
                $connectionClassObject = $this->_accessPointData->driver->connection;
                
                $this->_connection = new $connectionClassObject($this->_accessPointData->host, $this->_accessPointData->port, $this->_accessPointData->user, $this->_accessPointData->password, $this->_accessPointData->database);
                $this->_connection->Open();

            }
            
            /**
             * Геттер
             *
             * @param string $property
             * @return mixed
             */
            public function __get($property)
            {
                if($property == 'connection') {
                    return $this->_connection;
                }
                else {
                    return $this->Query('select * from '.$property);
                }

            }
            
            /**
             * Выполняет запрос в точку доступа
             *
             * ! Внимание
             * ! Для выполнения зарпоса с параметрами необходимо сделать следующее:
             * ! 1. параметры передаются в двойных квадратных скобках [[param:type]] где type=integer|double|string|blob
             * ! 2. параметры передаются в ассоциативном массиве, либо в виде stdClass
             * ! например: select * from test where id=[[id:integer]] and stringfield like [[likeparam:string]]
             * ! реальный запрос будет следующий с параметрами ['id' => '1', 'likeparam' => '%brbrbr%']: select * from test where id=1 and stringfield like '%brbrbr%'
             * ! запросы можно засунуть в коллекцию и выполнять с разными параметрами 
             * 
             * @param string $query 
             * @param stdClass $commandParams [page, pagesize, params, type = bigdata|noninfo|reader (default reader), returning = '']
             * @return mixed
             */
            public function Query($query, $commandParams = [])
            {
                // Превращаем параметры в обьект
                $commandParams = (object)$commandParams;

                $commandClassObject = $this->_accessPointData->driver->command; 
                $cmd = new $commandClassObject($query, $this->_connection);

                if (isset($commandParams->page)) {
                    $cmd->page = $commandParams->page;
                    $cmd->pagesize = isset($commandParams->pagesize) ? $commandParams->pagesize : 10;
                }

                if (isset($commandParams->params)) {
                    $cmd->params = (array)$commandParams->params;
                }

                // если не передали type то выставляем в reader
                if (!isset($commandParams->type)) {
                    $commandParams->type = self::QueryTypeReader;
                }

                if ($commandParams->type == self::QueryTypeReader) {
                    return $cmd->ExecuteReader();
                }
                else if($commandParams->type == self::QueryTypeBigData) {
                    return $cmd->ExecuteReader(false);
                }
                else if($commandParams->type == self::QueryTypeNonInfo) {
                    return $cmd->ExecuteNonQuery(isset($commandParams->returning) ? $commandParams->returning : '');
                }
                
            }
            
            /**
             * Вводит новую строку
             *
             * @param string $table название таблицы
             * @param array $row вводимая строка
             * @param string $returning название поля, значение которого необходимо вернуть (в случае с MySql можно опустить, будет возвращено значения поля identity)
             * @return NonQueryInfo
             */
            public function Insert($table, $row = array(), $returning = '')
            {
                $querybuilderClassObject = $this->_accessPointData->driver->querybuilder; 
                $queryBuilder = new $querybuilderClassObject();
                return $this->Query($queryBuilder->CreateInsert($table, $row), ['type' => self::QueryTypeNonInfo, 'returning' => $returning]);
            }

            /**
             * Вводит новую строку или обновляет старую, если совпали индексные поля
             * Отличный способ не задумываться над тем есть ли строка в базе данных или нет
             * Работает медленнее чем обычный ввод данных, поэтому использовать с осмотрительностью
             *
             * @param string $table таблица
             * @param array $row вводимая строка
             * @param array $exceptFields какие поля исключить из обновления в случае, если строка по идексным полям существует
             * @param string $returning название название поля, значение которого необходимо вернуть (в случае с MySql можно опустить, будет возвращено значения поля identity)
             * @return NonQueryInfo
             */
            public function InsertOrUpdate($table, $row = array(), $exceptFields = array(), $returning = '' /* used only in postgres*/)
            {
                $querybuilderClassObject = $this->_accessPointData->driver->querybuilder; 
                $queryBuilder = new $querybuilderClassObject();

                return $this->Query($queryBuilder->CreateInsertOrUpdate($table, $row, $exceptFields), ['type' => self::QueryTypeNonInfo, 'returning' => $returning]);
            }

            /**
             * Вводит кного строк разом
             *
             * @param string $table таблица 
             * @param array $rows вводимые строки
             * @return NonQueryInfo
             */
            public function InsertBatch($table, $rows = array())
            {

                $querybuilderClassObject = $this->_accessPointData->driver->querybuilder; 
                $queryBuilder = new $querybuilderClassObject();

                return $this->Query($queryBuilder->CreateBatchInsert($table, $rows), ['type' => self::QueryTypeNonInfo]);

            }
            
            /**
             * Обновляет строку
             *
             * @param string $table таблица
             * @param array $row обновляемая строка
             * @param string $condition условие обновления
             * @return NonQueryInfo
             */
            public function Update($table, $row, $condition)
            {

                $querybuilderClassObject = $this->_accessPointData->driver->querybuilder; 
                $queryBuilder = new $querybuilderClassObject();

                return $this->Query($queryBuilder->CreateUpdate($table, $condition, $row), ['type' => self::QueryTypeNonInfo]);
            }
            
            /**
             * Удалет строку по критериям
             *
             * @param string $table таблица
             * @param string $condition условие
             * @return NonQueryInfo
             */
            public function Delete($table, $condition = '')
            {

                $querybuilderClassObject = $this->_accessPointData->driver->querybuilder; 
                $queryBuilder = new $querybuilderClassObject();

                return $this->Query($queryBuilder->CreateDelete($table, $condition), ['type' => self::QueryTypeNonInfo]);

            }
            
            /**
             * Возвращает список таблиц в базе данных
             *
             * @return IDataReader
             */
            public function Tables()
            {

                $querybuilderClassObject = $this->_accessPointData->driver->querybuilder; 
                $queryBuilder = new $querybuilderClassObject();

                $reader = $this->Query($queryBuilder->CreateShowTables(), ['type' => self::QueryTypeReader]);
                if ($reader->Count() == 0) {
                    return null;
                }
                    
                return $reader;

            }
            
        }
    }
    
?>
