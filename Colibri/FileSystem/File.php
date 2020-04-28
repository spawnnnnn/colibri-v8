<?php

    namespace Colibri\FileSystem {

        use Colibri\AppException;
    use Colibri\Utils\Debug;

/**
         * Класс для работы с файлами
         * 
         * @property-read Attributes $attributes
         * @property-read string $filename
         * @property-read string $name
         * @property-read string $extension
         * @property-read string $directory
         * @property-read boolean $dotfile
         * @property-read string $path
         * @property-read int $size
         * @property-read boolean $exists
         * @property-read Security $access
         * @property-read string $content
         * @property-write boolean $created
         * @property-write boolean $midified
         * @property-write boolean $readonly
         * @property-write boolean $hidden
         * 
         */
        class File {

            private $attributes,
                    $info,
                    $_size = 0,
                    $access;

            /**
             * Конструктор
             *
             * @param string $path Путь к файлу
             */
            function __construct($path) {
                $this->info = Directory::PathInfo($path);
                if ($this->info['basename'] == '') {
                    throw new AppException('path argument is not a file path');
                }

                if ($this->info['dirname'] == '.'){
                    $this->info['dirname'] = '';
                }

            }

            public function __get($property){
                $return = null;
                switch (strtolower($property)){
                    case 'attributes': {
                        $return = $this->getAttributesObject();
                        break;
                    }
                    case 'filename': {
                        $return = $this->info['filename'];
                        break;
                    }
                    case 'name': {
                        $return = $this->info['basename'];
                        break;
                    }
                    case 'extension': {
                        if (array_key_exists('extension', $this->info)) {
                            $return = $this->info['extension'];
                        }
                        else {
                            $return = '';
                        }
                        break;
                    }
                    case 'directory': {
                        if ($this->info['dirname'] !== '') {
                            if (!($this->info['dirname'] instanceof Directory)) {
                                $this->info['dirname'] = new Directory($this->info['dirname'] . '/');
                            }
                            $return = $this->info['dirname'];
                        }
                        break;
                    }
                    case 'dotfile': {
                        $return = substr($this->name, 0, 1) == '.';
                        break;
                    }
                    case 'path': {
                        $dirname = $this->info['dirname'] instanceof Directory ? $this->info['dirname']->path : $this->info['dirname'];
                        $return = $dirname . ($dirname ? '/' : '') . $this->info['basename'];
                        break;
                    }
                    case 'size': {
                        if($this->_size == 0) {
                            $this->_size = filesize($this->path);
                        }
                        $return = $this->_size;
                        break;
                    }
                    case 'exists': {
                        $return = File::exists($this->path);
                        break;
                    }
                    case 'access': {
                        $return = $this->getSecurityObject();
                        break;
                    }
                    case 'content': {
                        if (File::exists($this->path)) {
                            $return = file_get_contents($this->path);
                        }
                        break;
                    }
                    default: {
                        if(strstr(strtolower($property), 'attr_') !== false) {
                            $p = str_replace('attr_', '', strtolower($property));
                            $return = $this->getAttributesObject()->$p;
                        }
                        break;
                    }

                }
                return $return;
            }

            function __set($property, $value){
                switch (strtolower($property)){
                    case 'created' :
                        $this->getAttributesObject()->created = $value;
                        break;
                    case 'modified' :
                        $this->getAttributesObject()->modified = $value;
                        break;
                    case 'readonly' :
                        $this->getAttributesObject()->readonly = $value;
                        break;
                    case 'hidden' :
                        $this->getAttributesObject()->hidden = $value;
                        break;
                    default: {
                        break;
                    }
                }
            }

            protected function getAttributesObject(){
                if ($this->attributes === null) {
                    $this->attributes = new Attributes($this);
                }

                return $this->attributes;
            }

            protected function getSecurityObject(){
                if ($this->access === null) {
                    $this->access = new Security($this);
                }

                return $this->access;
            }

            /**
             * Копирует файл
             *
             * @param string $path
             * @return void
             */
            public function CopyTo($path){
                File::copy($this->path, $path);
            }

            /**
             * Переместить файл
             *
             * @param string $path
             * @return void
             */
            public function MoveTo($path){
                File::move($this->path, $path);
            }

            /**
             * Возвращает имя файла
             *
             * @return string
             */
            public function ToString(){
                return $this->name;
            }

            /**
             * Считывает данные файл
             *
             * @param string $path
             * @return string
             */
            public static function Read($path) {
                if (File::Exists($path)) {
                    return file_get_contents($path);
                }
                return false;
            }
            
            /**
             * Записывает данные в файл
             *
             * @param string $path
             * @param string $content
             * @param boolean $recursive
             * @param integer $mode
             * @return void
             */
            public static function Write($path, $content, $recursive = false, $mode = 0777) {

                if(!File::Exists($path)) {
                    File::Create($path, $recursive, $mode);
                }

                file_put_contents($path, $content);

            }

            /**
             * Записывает данные в файл
             *
             * @param string $path
             * @param string $content
             * @param boolean $recursive
             * @param integer $mode
             * @return void
             */
            public static function Append($path, $content, $recursive = false, $mode = 0777) {

                if(!File::Exists($path)) {
                    File::Create($path, $recursive, $mode);
                }

                file_put_contents($path, $content, FILE_APPEND);

            }

            /**
             * Возвращает стрим файла
             *
             * @param string $path
             * @return FileStream
             */
            public static function Open($path){ //ireader
                if (File::Exists($path)) {
                    return new FileStream($path);
                }
                return false;
            }

            /**
             * Проверяет наличие файла
             *
             * @param string $path
             * @return boolean
             */
            public static function Exists($path){
                return file_exists($path);
            }
            
            /**
             * Проверяет пустой ли файл
             *
             * @param string $path
             * @return boolean
             */
            public static function IsEmpty($path){
                try { //use exception | file_exists ?
                    $info = stat($path);
                    return $info['size'] == 0;
                } catch (AppException $e){
                    return true;
                }
            }

            /**
             * Создает файл и возвращает стрим
             *
             * @param string $path
             * @param boolean $recursive
             * @param integer $mode
             * @return FileStream
             */
            public static function Create($path, $recursive = true, $mode = 0777){
                if(!Directory::Exists($path) && $recursive) {
                    Directory::Create($path, $recursive, $mode);
                }

                if(!File::Exists($path)) {
                    touch($path);
                }

                return new FileStream($path);
            }

            /**
             * Удаляет файл
             *
             * @param string $path
             * @return boolean
             */
            public static function Delete($path){
                if (!File::exists($path)) {
                    throw new AppException('file not exists');
                }

                return unlink($path);
            }

            /**
             * Копирует файла
             *
             * @param string $from
             * @param string $to
             * @return void
             */
            public static function Copy($from, $to){
                if (!File::exists($from)) {
                    throw new AppException('file not exists');
                }

                copy($from, $to);
            }

            /**
             * Переносит файл
             *
             * @param string $from
             * @param string $to
             * @return void
             */
            public static function Move($from, $to){
                if (!File::exists($from)) {
                    throw new AppException('source file not exists');
                }

                rename($from, $to);
            }

            /**
             * Проверяет не директория ли
             *
             * @param string $path
             * @return boolean
             */
            public static function IsDirectory($path) {
                return is_dir($path);
            }

            /**
             * Возвращает данные в виде массива
             *
             * @return array
             */
            public function ToArray() {
                return array(
                    'name' => $this->name,
                    'filename' => $this->filename,
                    'ext' => $this->extension,
                    'path' => $this->path,
                    'size' => $this->size,

                    'created' => $this->attr_created,
                    'modified' => $this->attr_modified,
                    'lastaccess' => $this->attr_lastaccess,
                );
            }


        }

    }