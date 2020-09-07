<?php

    /**
     * Encryption
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Encryption
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
             * @param string $stringifyMethod метод превращения в строку
             * @return string
             */
            static function Encrypt($key, $data, $stringifyMethod = 'base64') {
                $sha = hash('sha256', $key);
                $data = Rc4Crypt::Encrypt($sha, $data);
                return $stringifyMethod == 'hex' ? bin2hex($data) : base64_encode($data); 
            }
            
            /**
             * Расшифровать
             *
             * @param string $key ключ
             * @param string $data данные
             * @param string $stringifyMethod метод превращения в строку
             * @return string
             */
            static function Decrypt($key, $data, $stringifyMethod = 'base64') {
                $sha = hash('sha256', $key);
                $data = $stringifyMethod == 'hex' ? hex2bin($data) : base64_decode($data);
                return Rc4Crypt::Decrypt($sha, $data);
            }
            
        }


    }