<?php
    /**
     * Colibri
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri
     * 
     */
    namespace Colibri {

        use Psr\Container\NotFoundExceptionInterface;
        use Throwable;

        /**
         * Класс исключения для приложения
         */
        class AppException extends \Exception implements NotFoundExceptionInterface {

            /**
             * Данные приложения
             *
             * @var mixed
             */
            private $_appData;

            /**
             * Конструктор
             *
             * @param string $message
             * @param int $code
             * @param mixed $appData
             * @param Throwable $previous
             */
            public function __construct($message, $code = 0, $appData = null, Throwable $previous = null)
            {
                parent::__construct($message, $code, $previous);
                $this->_appData = $appData;
            }

            /**
             * Возвращает данные приложения
             *
             * @return mixed
             */
            public function getAppData() {
                return $this->_appData;
            }

        }

    }