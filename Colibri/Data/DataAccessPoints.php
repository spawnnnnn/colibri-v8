<?php

    /**
     * Доступ к базе данных
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Data
     * 
     * Пример: 
     * 
     * try {
     * 
     *      $accessPoint = App::$dataAccessPoints->Get('main');
     *      
     *      # Получение данных по запросу
     * 
     *      class Queries {
     *          const TestSelectQuery = 'select * from test where id=[[id:integer]] and text=[[text:string]] and dbl=[[dbl::double]]';
     *      }
     *      $reader = $accessPoint->Query(Queries::TestSelectQuery, ['page' => 1, 'pagesize' => 10, 'params' => ['id' => 1, 'text' => 'adfadf', 'dbl' => 1.1]]);
     *      while($result = $reader->Read()) {
     *          print_r($result); // обьект
     *      }
     *      
     *      # или без параметров
     * 
     *      $reader = $accessPoint->Query('select * from test where id=\'2\' and text=\'adfasdfasdf\' and dbl=\'1.1\'', ['page' => 1, 'pagesize' => 10]);
     *      while($result = $reader->Read()) {
     *          print_r($result); // обьект
     *      }
     *
     *      $accessPoint->Query('BEGIN');
     * 
     *      # если необходимо выполнить запрос insert, update или delete 
     *      $nonQueryInfo = $accessPoint->Query('delete from test where id=1', ['type' => DataAccessPoint::QueryTypeNonInfo]);
     *      
     *      # если необходимо выполнить запрос с большим количеством данных, например для запросов с автоподкачкой 
     *      $reader = $accessPoint->Query('select * from test', ['page' => 1, 'pagesize' => 100, 'type' => DataAccessPoint::QueryTypeBigData]);
     *  
     *      # ввод данных
     *      $nonQueryInfo = $accessPoint->Insert('test', ['text' => 'адфасдфасдфасдф', 'dbl' => 1.1], 'id'); # только для postgresql
     *      # возвращается класс NonQueryInfo, для postgres необходимо передать дополнительный параметр returning - название поля, которое нужно вернуть
     * 
     *      # обновление данных     
     *      $returnsBool = $accessPoint->Update('test', ['text' => 'adfasdfasdf', 'dbl' => 1.2], 'id=1');
     *      # возвращает true если обновление прошло успешно
     * 
     *      # ввод с обновлением данных, если есть дубликат по identity полю или sequence для postgresql
     *      $nonQueryInfo = $accessPoint->InsertOrUpdate('test', ['id' => 1, 'text' => 'adfadsfads', 'dbl' => 1.1], ['id', 'text'], 'id'); 
     *      # поле returning нужно только для postgresql
     *      # возвращается класс NonQueryInfo, для postgres необходимо передать дополнительный параметр returning - название поля, которое нужно вернуть
     * 
     *      # ввод данны пачкой
     *      $nonQueryInfo = $accessPoint->InsertBatch('test', [ ['text' => 'adsfasdf', 'dbl' => 1.0], ['text' => 'adsfasdf', 'dbl' => 1.1] ]);
     * 
     *      $accessPoint->Query('COMMIT');
     * 
     *      # удаление данных
     *      $returnsBool = $accessPoint->Delete('test', 'id=1');
     *      # возвращает true если удаление прошло успешно, нужно учесть, что если не передать параметр condition то будет выполнено truncate table test
     * 
     *      # получение списка таблиц
     *      $tablesReader = $accessPoint->Tables();
     *      # возвращает IDataReader 
     *
     * }
     * catch(DataAccessPointsException $e) {
     *      print_r($e);
     * }
     * 
     * 
     */
    namespace Colibri\Data {

        use Colibri\App;

        /**
         * Класс фабрика для создания точек доступа
         * 
         * @property-read array $accessPoints
         * @property-read array $pool
         * 
         */
        class DataAccessPoints {

            /**
             * Синглтон
             *
             * @var DataAccessPoints
             */
            public static $instance;

            /**
             * Список точек доступа
             *
             * @var array
             */
            private $_accessPoints;

            /**
             * Список открытых точек доступа
             *
             * @var array
             */
            private $_accessPointsPool;

            /**
             * Конструктор
             */
            public function __construct() {

                $this->_accessPointsPool = [];
                $this->_accessPoints = App::$config->Query('databases.access-points', (object)[])->AsObject();

            }

            /**
             * Статический конструктор
             *
             * @return DataAccessPoints
             */
            public static function Create() {
                
                if(self::$instance) {
                    return self::$instance; 
                }
                self::$instance = new DataAccessPoints();
                return self::$instance;

            }

            /**
             * Создает точку доступа
             *
             * @param string $name
             * @return DataAccessPoint
             */
            public function Get($name) {

                if(isset($this->_accessPointsPool[$name])) {
                    $return = $this->_accessPointsPool[$name];
                }

                if(isset($this->_accessPoints->points) && isset($this->_accessPoints->points->$name)) {
                    
                    // берем данные точки доступа
                    $accessPointData = $this->_accessPoints->points->$name;

                    $accessPointConnection = $accessPointData->connection;
                    if(!isset($this->_accessPoints->connections->$accessPointConnection)) {
                        throw new DataAccessPointsException('Unknown access point connection');
                    }

                    $accessPointType = $this->_accessPoints->connections->$accessPointConnection->type;
                    if(!isset($this->_accessPoints->drivers->$accessPointType)) {
                        throw new DataAccessPointsException('Unknown access point type');
                    }

                    $database = $accessPointData->database;

                    // формируем данные для инициализации точки доступа
                    $accessPointInit = (object)[
                        'host' => $this->_accessPoints->connections->$accessPointConnection->host,
                        'port' => $this->_accessPoints->connections->$accessPointConnection->port,
                        'user' => $this->_accessPoints->connections->$accessPointConnection->user,
                        'password' => $this->_accessPoints->connections->$accessPointConnection->password,
                        'database' => $database,
                        'driver' => $this->_accessPoints->drivers->$accessPointType
                    ];

                    $return = new DataAccessPoint($accessPointInit);

                }
                else {
                    throw new DataAccessPointsException('Unknown access point type');
                }

                return $return;

            }

            /**
             * Геттер
             *
             * @param string $property
             * @return void
             */
            public function __get($property) {
                $property = strtolower($property);
                $return = null;
                if($property == 'accesspoints') {
                    $return = $this->_accessPoints;
                }
                else if($property == 'pool') {
                    $return = $this->_accessPointsPool;
                }
                else {
                    $return = $this->Get($property);
                }
                return $return;

            }

        }

    }

?>