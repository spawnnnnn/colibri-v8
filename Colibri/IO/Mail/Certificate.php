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
         */
        class Certificate
        {
            private $_cert_file = "";
            private $_key_file  = "";
            private $_key_pass  = "";
            
            public function __construct($file, $keyfile, $keypass)
            {
                $this->_cert_file = $file;
                $this->_key_file = $keyfile;
                $this->_key_pass = $keypass;
            }
            
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
