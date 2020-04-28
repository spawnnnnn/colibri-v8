<?php

    namespace Colibri\Utils\Logs {

        use Colibri\Helpers\Date;

        /**
         * Класс для работы с логами в памяти
         */
        class MemoryLogger extends Logger {

            /**
             * Конструктор
             */
            public function __construct($maxLogLevel = 7, $dummy = []) {
                $this->_maxLogLevel = $maxLogLevel;
                $this->_device = [];
            }

            /**
             * Пишет в лог строку
             *
             * @param array ...$args
             * @return void
             */
            public function WriteLine($level, $data) {
                $args = !is_array($data) ? [$data] : $data;
                $args = Date::ToDbString(microtime(true), '%Y-%m-%d-%H-%M-%S-%f')."\t".implode("\t", $args);
                $this->_device[] = $args;
            }

            /**
             * Возвращает данные лога в виде массива
             *
             * @return array
             */
            public function Content() {
                return $this->_device;
            }
            

        }

    }