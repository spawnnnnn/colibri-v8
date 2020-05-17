<?php
    /**
     * Mail
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Mail
     */
    namespace Colibri\IO\Mail {

        /**
         * Сертификат
         * 
         * @property-read string $file файл сертификата
         * @property-read string $key файл ключа сертификата
         * @property-read string $pass пароль к сертификату
         * 
         */
        class Certificate
        {
            /**
             * Файл сертификата
             *
             * @var string
             */
            private $_cert_file = "";

            /**
             * Файл ключа сертификата
             *
             * @var string
             */
            private $_key_file  = "";

            /**
             * Пароль к сертификату
             *
             * @var string
             */
            private $_key_pass  = "";
            
            /**
             * Конструктор
             *
             * @param string $file файл сертификата
             * @param string $keyfile файл ключа сертификата
             * @param string $keypass пароль к сертификату
             */
            public function __construct($file, $keyfile, $keypass)
            {
                $this->_cert_file = $file;
                $this->_key_file = $keyfile;
                $this->_key_pass = $keypass;
            }
            
            /**
             * Геттер
             *
             * @param string $property свойство
             * @return mixed
             */
            public function __get($property)
            {
                switch (strtolower($property)) {
                    case 'file':
                        return $this->_cert_file;
                    case 'key':
                        return $this->_key_file;
                    case 'pass':
                        return $this->_key_pass;
                    default: {
                        break;
                    }
                }
            }
        }

    }
