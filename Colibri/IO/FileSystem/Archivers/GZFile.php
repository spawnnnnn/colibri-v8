<?php

    /**
     * Archivers
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\FileSystem\Archivers
     */
    namespace Colibri\IO\FileSystem\Archivers {

        use Colibri\IO\FileSystem\File;

        /**
         * Работа с архивом GZip
         * Враппер для функций gz...
         */
        class GZFile {
        
            /**
             * Поинтер на файл
             *
             * @var resource
             */
            private $pointer;
            
            /**
             * Конструктор
             *
             * @param string $filename название файла
             * @param int $mode режим открытия файла
             */
            public function __construct($filename, $mode = File::MODE_READ) {
                $this->pointer = gzopen($filename, $mode);
            }
            
            /**
             * Считать определенное количество байт
             *
             * @param integer $length количество байт
             * @return string полученные данные
             */
            public function Read($length = 255) {
                return gzgets($this->pointer, $length);
            }
            
            /**
             * Считать все
             *
             * @return string
             */
            public function ReadAll() {
                $sret = "";
                while($s = $this->Read()) {
                    $sret .= $s;
                }
                return $sret;
            }
    
            /**
             * Записать определенное количество байт
             *
             * @param string $string строка для записи
             * @param int $length количество байт для записи
             * @return int количество записанных байт
             */
            public function Write($string, $length = null) {
                return gzputs($this->pointer, $string, $length || strlen($string));
            }
            
            /**
             * Закрыть файл
             *
             * @return void
             */
            public function Close() {
                gzclose($this->pointer);
            }
    
        }

    }