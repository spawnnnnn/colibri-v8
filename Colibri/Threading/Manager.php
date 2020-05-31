<?php
    /**
     * Threading
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2020 ColibriLab
     * @package Colibri\Threading
     * 
     */
    namespace Colibri\Threading {

        use Colibri\App;

        /**
         * Singleton для обработки и создания процессов
         */
        class Manager
        {

            /**
             * Синглтон
             * @var Manager
             */
            protected static $instance;

            /**
             * Конструктор, запускает обработку воркера, если задан
             */
            public function __construct()
            {
                $this->_processWorkers();
            }

            /**
             * Статическая функция создания Singleton
             *
             * @return void
             */
            public static function Instance()
            {
                if (!self::$instance) {
                    self::$instance = new self();
                }
                return self::$instance;
            }

            /**
             * Запускает обработку воркера
             *
             * @return void
             */
            private function _processWorkers()
            {
                if (App::Request()->get->worker) {
                    $worker = Worker::Unserialize(App::Request()->get->worker);
                    $worker->Prepare(App::Request()->get->params);
                    $worker->Run();
                    exit;
                }
            }

            /**
             * Создает процесс для заданного воркера
             *
             * @param Worker $worker воркер, который нужно запустить
             * @return Process созданный процесс
             */
            public function CreateProcess(Worker $worker)
            {
                return new Process($worker);
            }
        }


    }
