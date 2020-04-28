<?php
    /**
     * Шифрование методом RC4 
     * 
     * @author Ваган Григорян <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Helpers
     * @version 1.0.0
     * 
     */
    namespace Colibri\Encryption {
        
        /**
         * Шифрование методом RC4 
         */
        class Rc4Crypt {

            /**
             * Зашифровать
             *
             * @param string $pwd
             * @param string $data
             * @return string
             */
            static function Encrypt($pwd, $data) {
                $key[] = '';
                $box[] = '';
                $cipher = '';
                $pwd_length = strlen($pwd);
                $data_length = strlen($data);

                for ($i = 0; $i < 256; $i++) {
                    $key[$i] = ord($pwd[$i % $pwd_length]);
                    $box[$i] = $i;
                }
                $j = 0;
                for ($i = 0; $i < 256; $i++) {
                    $j = ($j + $box[$i] + $key[$i]) % 256;
                    $tmp = $box[$i];
                    $box[$i] = $box[$j];
                    $box[$j] = $tmp;
                }
                $a = $j = 0;
                for ($i = 0; $i < $data_length; $i++) {
                    $a = ($a + 1) % 256;
                    $j = ($j + $box[$a]) % 256;

                    $tmp = $box[$a];
                    $box[$a] = $box[$j];
                    $box[$j] = $tmp;

                    $k = $box[(($box[$a] + $box[$j]) % 256)];
                    $cipher .= chr(ord($data[$i]) ^ $k);
                }
                return $cipher;
            }

            /**
             * Расшифровать
             *
             * @param string $pwd
             * @param string $data
             * @return string
             */
            static function Decrypt ($pwd, $data) {
                return rc4crypt::encrypt($pwd, $data);
            }
            
        }    
}


?>