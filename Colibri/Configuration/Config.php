<?php

    /**
     * Configuration
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Configuration
     *
     */
    namespace Colibri\Configuration {

        use Colibri\App;
        use Colibri\Helpers\Objects;
        use Colibri\Helpers\Variable;
        use Colibri\FileSystem\File;

        /**
         * Класс для работы с конфиг файлами в yaml
         */
        class Config
        {

            const Yaml      = 'yml';
            const Xml       = 'xml'; 

            /**
             * Тут хранятся загруженные данные конфиг файлами
             *
             * @var stdClass
             */
            private $_configData;

            /**
             * Тип конфигурации
             *
             * @var string
             */
            private $_type;


            /**
             * Конструктор
             *
             * @param mixed $fileName файл или данные
             * @param string $type тип конфигурации
             */
            public function __construct($fileName, string $type = '')
            {
                $this->_type = $type;
                $driverClass = $this->_detectDriver($type ? $type : $fileName);
                $this->_configData = new $driverClass($fileName);
            }

            /**
             * Статический конструктор
             *
             * @param mixed $fileName файл или данные
             * @param string $type тип конфигурации
             * @return Config
             */
            public static function Create($fileName, string $type = '') {
                return new Config($fileName, $type);
            }

            /**
             * Детектируем по типу файла класс драйвера
             *
             * @param mixed $fileName
             * @return string
             * @throws ConfigException
             */
            private function _detectDriver($fileName) {

                $type = $fileName;
                if(File::Exists($type)) {
                    $file = new File($type);
                    $type = $file->extension;
                    $this->_type = $type;
                }

                $className = 'Colibri\\Configuration\\Drivers\\'.$type.'ConfigDriver';
                if(class_exists($className)) {
                    return $className;
                }

                throw new ConfigException('Can not detect driver. Type: '.$type);
            }

            /**
             * Запрос значения из конфигурации
             *
             * пути указываются в javascript нотации
             * например: settings.item[0].info или settings.item.buh.notice_email
             *
             * @param string $item строковое представление пути в конфигурационном файле
             * @param mixed $default значение по умолчанию, если путь не найден
             * @return mixed
             */
            public function Query($item, $default = null)
            {
                $data = $this->_configData->Read($item, $default);
                if (is_array($data) && !Variable::IsAssociativeArray($data)) {
                    return new ConfigItemsList($data, $this->_type);
                } else {
                    return new Config($data, $this->_type);
                }

            }

            /**
             * Вернуть внутренние данные в виде обьекта
             *
             * @return stdClass
             */
            public function AsObject()
            {
                return $this->_configData->AsObject();
            }

            /**
             * Вернуть внутренние данные в виде массива
             *
             * @return array
             */
            public function AsArray()
            {
                return $this->_configData->AsArray();
            }

            public function AsRaw() {
                return $this->_configData->AsRaw();
            }

            /**
             * Вернуть хранимое значение
             * Внимание! Если текущие данные массив или обьект, то будет возвращен null
             *
             * @return mixed
             */
            public function GetValue()
            {
                return $this->_configData->GetValue();
            }   

        }

        



    }
