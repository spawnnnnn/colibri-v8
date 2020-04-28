<?php

    /**
     * Шифрование
     * 
     * @author Ваган Григорян <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Helpers
     * @version 1.0.0
     * 
     */
    namespace Colibri\Encryption {
        
        /**
         * Шифрование
         */
        class Crypt {
            
            /**
             * Зашифровать
             *
             * @param string $key ключ
             * @param string $data данные
             * @return string
             */
            static function Encrypt($key, $data) {
                $sha = hash('sha256', $key);
                $data = Rc4Crypt::Encrypt($sha, $data);
                return bin2hex($data);    
            }
            
            /**
             * Расшифровать
             *
             * @param string $key ключ
             * @param string $data данные
             * @return string
             */
            static function Decrypt($key, $data) {
                $sha = hash('sha256', $key);
                $data = hex2bin($data);
                return Rc4Crypt::Decrypt($sha, $data);
            }
            
        }


    }