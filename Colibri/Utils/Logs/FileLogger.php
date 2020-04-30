<?php
    /**
     * Logs
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2020 ColibriLab
     * @package Colibri\Utils\Logs
     * 
     */
    namespace Colibri\Utils\Logs {

        use Colibri\App;
        use Colibri\Helpers\Date;
        use Colibri\IO\FileSystem\File;

        /**
         * Лог файл
         */
        class FileLogger extends Logger {

            /**
             * Конструктор
             *
             * @param int $maxLogLevel Уровень логирования
             * @param mixed $device название файла
             */
            public function __construct($maxLogLevel = 7, $device = '') {
                $this->_device = !$device ? App::$appRoot.'_cache/unnamed.log' : $device;
                $this->_maxLogLevel = $maxLogLevel;
            }

            /**
             * Записывает в лог данные
             *
             * @param int $level уровень ошибки
             * @param mixed $data данные
             * @return void
             */
            public function WriteLine($level, $data) {

                if($level > $this->_maxLogLevel) {
                    return ;
                }

                if(!File::Exists($this->_device)) {
                    File::Create($this->_device, true, 0777);
                }

                $args = !is_array($data) ? [$data] : $data;
                $args[] = "\n";
                $args = Date::ToDbString(microtime(true), '%Y-%m-%d-%H-%M-%S-%f')."\t".implode("\t", $args);

                $fi = new File($this->_device);
                if($fi->size > 1048576) {
                    File::Move($this->_device, $this->_device.'.'.Date::ToDbString(microtime(true), '%Y-%m-%d-%H-%M-%S-%f'));
                    File::Create($this->_device, true, 0777);
                }

                File::Append($this->_device, $args);
                
            }

            /**
             * Возвращает контент лог файла
             *
             * @return mixed
             */
            public function Content() {
                return File::Read($this->_device);
            }

        }

    }