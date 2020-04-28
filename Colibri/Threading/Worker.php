<?php

    namespace Colibri\Threading {

        use Colibri\App;
        use Colibri\Helpers\Randomization;
        use Colibri\Helpers\Variable;
        use Colibri\Utils\Logs\FileLogger;
        use Colibri\Utils\Logs\Logger;

        /**
         * Класс работы в процессами, имитирует поток
         * Для работы необходимо наличие php-cli, memcached и ramdisk
         */
        abstract class Worker {

            /**
             * Лимит по времени на выполнение процесса
             *
             * @var integer
             */
            protected $_timeLimit = 0;

            /**
             * Приоритет процесса, требуется наличие nohup
             *
             * @var integer
             */
            protected $_prio = 0;

            /**
            * Ключ необходим для идентификации процесса в списке процессов в ps
            *
            * @var string
            */
            protected $_key = '';

            /**
             * ID потока
             *
             * @var string
             */
            protected $_id = '';

            /**
             * Лог воркера
             *
             * @var LogDevice
             */
            protected $_log;

            /**
             * Переданные в воркер параметры
             *
             * @var mixed
             */
            protected $_params;

            /**
             * Создает обьект класса Worker
             *
             * @param integer $timeLimit лимит по времени для выполнения воркера
             * @param integer $prio приоритет, требуется наличие nohup
             */
            public function __construct($timeLimit = 0, $prio = 0, $key = '') {
                $this->_timeLimit = $timeLimit;
                $this->_prio = $prio;

                $this->_key = $key ? $key : uniqid();
                $this->_id = Randomization::Integer(0, 999999999);

                $mode = App::$config ? App::$config->Query('mode')->GetValue() : App::ModeDevelopment;
                $this->_log = new FileLogger($mode == App::ModeDevelopment ? Logger::Debug : Logger::Error, 'worker_log_'.$this->_id); // лог файл не режется на куски
            }

            /**
             * Работа по процессу/потоку, необходимо переопределить
             *
             * @return void
             */
            abstract public function Run();

            /**
             * функция Getter для получения данных по потоку
             *
             * @param string $prop
             * @return void
             */
            public function __get($prop) {
                $return = null;
                $prop = strtolower($prop);
                switch($prop) {
                    case 'id': $return =  $this->_id; break;
                    case 'timelimit': $return =  $this->_timeLimit; break;
                    case 'prio': $return =  $this->_prio; break;
                    case 'log': $return =  $this->_log; break;
                    case 'key': $return =  $this->_key; break;
                    default: throw new Exception(ErrorCodes::UnknownProperty, $prop);
                }
                return $return;
            }

            /**
             * функция Setter для ввода данных в процесс
             *
             * @param string $prop
             * @param mixed $val
             */
            public function __set($prop, $val) {
                $prop = strtolower($prop);
                switch($prop) {
                    case 'timelimit': $this->_timeLimit = $val; break;
                    case 'prio': $this->_prio = $val; break;
                    default: throw new Exception(ErrorCodes::UnknownProperty, $prop); break;
                }
            }

            /**
             * Подготавливает параметры к отправке в поток
             *
             * @param mixed $params
             * @return void
             */
            public function PrepareParams($params) {
                return Variable::Serialize($params);
            }

            /**
             * Разбирает параметры из строки в объект
             *
             * @return void
             */
            public function Prepare($params) {
                $this->_params = Variable::Unserialize($params);
            }

            /**
             * Сериализует воркер
             *
             * @return void
             */
            public function Serialize() {
                return Variable::Serialize($this);
            }

            /**
             * Десериализует воркер
             *
             * @param string $workerString строка содержащая сериализованный воркер
             * @return Worker десериализованный воркер
             */
            public static function Unserialize(string $workerString) {
                return Variable::Unserialize($workerString);
            }

            public function Exists() {

                $output = '';
                $code = 0;

                exec("/bin/ps -auxww | /bin/grep ".$this->_key." | /bin/grep -v grep", $output, $code);
                if($code!=0 && $code!=1) {
                    return false;
                }
                if(count($output) > 0) {
                    return true;
                }

                return false;

            }

        }

    }