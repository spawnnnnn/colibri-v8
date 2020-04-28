<?php

    namespace Colibri\Data\Models {

        use ArrayAccess;
        use Colibri\App;
        use Colibri\Collections\ObjectList;
        use Colibri\Data\DataAccessPoint;
        use Colibri\Data\SqlClient\IDataReader;
        use Colibri\Helpers\Variable;
        use Colibri\Utils\Debug;
        use Colibri\Utils\ObjectEx;
        use Countable;
    use Traversable;

/**
         * Представление таблицы данных
         * 
         * @property-read boolean $hasrows
         * @property-read integer $count
         * @property-read integer $affected
         * @property-read integer $loaded
         * @method mixed methodName()
         */
        class DataTable implements Countable, ArrayAccess, \IteratorAggregate {
            
            /**
             * Точка доступа
             *
             * @var DataAccessPoint
             */
            protected $_point;

            /**
             * Ридер    
             *
             * @var IDataReader
             */
            protected $_reader;
            /**
             * Кэш загруженных строк
             *
             * @var ObjectList
             */
            protected $_cache;
            /**
             * Название класса представления строк
             *
             * @var ObjectEx
             */
            protected $_returnAs;
            
            /**
             * Конструктор
             *
             * @param DataAccessPoint $point
             * @param IDataReader $reader
             * @param string $returnAs
             */
            public function __construct(DataAccessPoint $point, IDataReader $reader = null, $returnAs = 'Colibri\\Data\\Models\\DataRow') {
                $this->_point = $point;
                $this->_reader = $reader;
                $this->_cache = new ObjectList();
                $this->_returnAs = $returnAs;
            }

            /**
             * Статический конструктор
             *
             * @return DataTable
             */
            public static function Create($point) {
                if(is_string($point)) {
                    $point = App::$dataAccessPoints->Get($point);
                }
                return new DataTable($point);
            }

            /**
             * Возвращает итератор
             *
             * @return DataTableIterator
             */
            public function getIterator()
            {
                return new DataTableIterator($this);
            }

            /**
             * Загружает данные из запроса или таблицы
             * 
             * @param string $query название таблицы или запрос
             * @param string $params
             * @return DataTable
             */
            public function Load($query, $params = []) {
                $params = (object)$params;

                if($this->_reader) {
                    $this->_reader->Close();
                }

                if(strstr($query, 'select') === false) {
                    // если нет слова select, то это видимо название таблицы, надо проверить
                    if(strstr($query, ' ') !== false) {
                        // есть пробел, значит это не название таблицы нужно выдать ошибку
                        throw new DataModelException('Param query can be only the table name or select query');
                    }
                    else {
                        $query = 'select * from '.$query;
                        $condition = [];
                        if(isset($params->params)) {
                            foreach($params->params as $key => $value) {
                                $condition[] = $key.'=[['.$key.']]';
                            }
                        }
                        if(count($condition) > 0) {
                            $query .= ' where '.implode(' and ', $condition);
                        }
                    }
                }

                $this->_reader = $this->_point->Query($query, $params);
                return $this;
            }

            /**
             * Возвращает количество строк
             *
             * @return int
             */
            public function Count() {
                return $this->_reader->Count();
            }
            
            /**
             * Возвращает общее количество строк
             *
             * @return int
             */
            public function Affected() {
                return $this->_reader->Affected();
            }

            /**
             * Возвращает наличие строк
             *
             * @return boolean
             */
            public function HasRows() {
                return $this->_reader->HasRows();
            }

            /**
             * Список полей
             *
             * @return void
             */
            public function Fields() {
                return Variable::ChangeArrayKeyCase($this->_reader->Fields(), CASE_LOWER);
            }

            /**
             * Возвращает точку доступа
             *
             * @return DataAccessPoint
             */
            public function Point() {
                return $this->_point;
            }
            
            /**
             * Создает обьект данных представления строки
             *
             * @param ObjectEx $result
             * @return mixed
             */
            protected function _createDataRowObject($result) {
                
                $className = $this->_returnAs;
                
                // ищем класс, если нету то добавляем неймспейс App\Models
                if(!class_exists($className)) {
                    $className = 'App\\Models\\'.$className;
                    // ищем модель в приложении, если не нашли то берем стандартную модель
                    if(!class_exists($className)) {
                        $className = 'Colibri\\Data\\Models\\DataRow';
                    }
                }

                return new $className($this, $result);
            }
            
            /**
             * Считывает еще одну строку из источника
             *
             * @return mixed
             */
            protected function _read() {
                return $this->_createDataRowObject(
                    $this->_reader->Read()
                );
            }
            
            /**
             * Считывает строки до указнного индекса
             *
             * @param integer $index
             * @return mixed
             */
            protected function _readTo($index) {
                while($this->_cache->Count() < $index) {
                    $this->_cache->Add($this->_read());
                }
                return $this->_cache->Add($this->_read());
            }
            
            /**
             * Возвращает строку по выбранному индексу
             *
             * @param integer $index
             * @return mixed
             */
            public function Item($index) {
                if($index >= $this->_cache->Count()) {
                    return $this->_readTo($index);
                }
                else {
                    return $this->_cache->Item($index);
                }
            }
            
            /**
             * Возвращает первую строку
             *
             * @return mixed
             */
            public function First() {
                return $this->Item(0);
            }
            
            /**
             * Скачивает и кэширует все
             *
             * @param boolean $closeReader
             * @return mixed[]
             */
            public function CacheAll($closeReader = true) {
                $this->_readTo($this->Count() - 1);
                if($closeReader) {
                    $this->_reader->Close();
                }
                return $this->_cache;
            }
            
            /**
             * Создает пустую строку
             *
             * @return mixed
             */
            public function EmptyRow() {
                return $this->_createDataRowObject(new \stdClass());
            }
            
            /**
             * Устанавливает строку по выбранному индексу в кэш
             *
             * @param integer $index
             * @param ObjectEx $data
             * @return void
             */
            public function Set($index, $data) {
                $this->_cache->Set($index, $data);
            }

            /**
             * Возвращает таблицу в виде массива
             *
             * @param boolean $noPrefix
             * @return void
             */
            public function ToArray($noPrefix = false) {
                $table = $this->CacheAll(false);
                $ret = [];
                foreach($table as $row) {
                    $ret[] = $row->ToArray($noPrefix);
                }
                return $ret;
            }

            public function Save() {
                foreach($this as $row) {
                    $row->Save();
                } 
            }

            public function Delete() {
                foreach($this as $row) {
                    $row->Delete();
                }
            }

            /**
             * @param int $offset
             * @param DataRow $value
             * @return void
             */
            public function offsetSet($offset, $value) {
                if (is_null($offset)) {
                    $this->_cache->Add($value);
                } else {
                    $this->_cache->Set($offset, $value);
                }
            }
        
            /**
             * @param int $offset
             * @return bool
             */
            public function offsetExists($offset) {
                return $offset < $this->_cache->Count();
            }
        
            /**
             * @param int $offset
             * @return void
             */
            public function offsetUnset($offset) {
                $this->_cache->DeleteAt($offset);
            }
        
            /**
             * Возвращает значение по индексу
             *
             * @param int $offset
             * @return DataRow
             */
            public function offsetGet($offset) {
                return $this->Item($offset);
            }
            
        }

    }