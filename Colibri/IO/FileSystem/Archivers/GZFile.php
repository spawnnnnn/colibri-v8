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

        class GZFile {
        
            private $pointer;
            
            public function __construct($filename, $mode = File::MODE_READ) {
                $this->pointer = gzopen($filename, $mode);
            }
            
            public function Read($length = 255) {
                return gzgets($this->pointer, $length);
            }
            
            public function ReadAll() {
                $sret = "";
                while($s = $this->Read()) {
                    $sret .= $s;
                }
                return $sret;
            }
    
            public function Write($string, $length = null) {
                return gzputs($this->pointer, $string, $length || strlen($string));
            }
            
            public function Close() {
                gzclose($this->pointer);
            }
    
        }

    }