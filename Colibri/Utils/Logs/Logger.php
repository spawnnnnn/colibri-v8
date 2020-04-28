<?php

    namespace Colibri\Utils\Logs {

        use Colibri\App;
        use Colibri\Helpers\Date;
        use Colibri\Helpers\TimeZone;
    use Colibri\Configuration\Config;
    use Colibri\FileSystem\File;

        /**
         * Лог файл
         */
        abstract class Logger {

            /** Обязательное сообщение */
            const Emergency = 0;    
            /** Информация */
            const Alert = 1;
            /** Критическая ошибка */
            const Critical = 2;   
            /** Ошибка */ 
            const Error = 3;
            /** Предупреждение */
            const Warning = 4;
            /** Информирование */    
            const Notice = 5;
            /** Просто информация */
            const Informational = 6;
            /** Дебаг */    
            const Debug = 7;

            /**
             * Наименование лог файла
             *
             * @var mixed
             */
            protected $_device;

            /**
             * Максимальный уровень логирования
             *
             * @var integer
             */
            protected $_maxLogLevel = 7;

            /**
             * Записывает в лог данные
             *
             * @param int $level уровень ошибки
             * @param mixed $data данные
             * @return void
             */
            abstract public function WriteLine($level, $data);

            /**
             * Возвращает контент лог файла
             *
             * @return mixed
             */
            abstract public function Content();

            /**
             * Фабрика
             *
             * @param Config $loggerConfig
             * @return Logger
             */
            public static function Create(Config $loggerConfig) {
                $loggerType = $loggerConfig->Query('type')->GetValue();
                $className = 'Colibri\\Utils\\Logs\\'.$loggerType.'Logger';
                if(!\class_exists($className)) {
                    throw new LoggerException('Invalid logger type');
                }

                return new $className($loggerConfig->Query('level')->GetValue(), $loggerConfig->Query('device')->AsObject());

            }

        }

    }