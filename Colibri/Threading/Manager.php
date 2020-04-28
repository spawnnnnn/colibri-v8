<?php

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
            public static $instance;

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
            public static function Create()
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
                if (App::$request->get->worker) {
                    $worker = Worker::Unserialize(App::$request->get->worker);
                    $worker->Prepare(App::$request->get->params);
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
