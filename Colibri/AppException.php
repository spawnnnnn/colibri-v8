<?php

    namespace Colibri {

        use Throwable;

        class AppException extends \Exception {

            private $_appData;

            /**
             * Constructor
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
             * Returns custom app data 
             *
             * @return mixed
             */
            public function getAppData() {
                return $this->_appData;
            }

        }

    }