<?php
    /**
     * Mail
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Mail
     */
    namespace Colibri\IO\Mail {

        use Colibri\AppException;

        /**
         * Исключение
         */
        class Exception extends AppException {
            
            public function ToString() {
                return '<strong>' . $this->message . "</strong><br />\n";
            }
            
        }

    }