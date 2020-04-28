<?php

    namespace Colibri\FileSystem {

        use Colibri\AppException;

        /**
         * Класс для работы с директориями
         * 
         * @property-read Attributes $attributes
         * @property-read string $name
         * @property-read string $path
         * @property-read boolean $dotfile
         * @property-read void $size
         * @property-read Directory $parent
         * @property-read Security $access
         * @property-write boolean $created
         * @property-write boolean $midified
         * @property-write boolean $readonly
         * @property-write boolean $hidden     
         * 
         */
        class Directory {

            private $attributes,
                    $path,
                    $parent,
                    $access,
                    $pathArray;

            /**
             * Конструктор
             *
             * @param string $path
             */
            function __construct($path){
                $this->path = dirname($path[strlen($path) - 1] == '/' ? $path . '#' : $path);
            }

            public function __get($property) {
                $return = null;
                switch (strtolower($property)){
                    case 'current':
                    case 'size': {
                        $return = null;
                        break;
                    }
                    case 'attributes' :{
                        $return = $this->getAttributesObject();
                        break;
                    }
                    case 'name' :{
                        if(!$this->pathArray) {
                            $this->pathArray = explode('/', $this->path);
                        }
                        $return = $this->pathArray[count($this->pathArray) - 1];
                        break;
                    }
                    case 'path' :{
                        $return = $this->path.'/';
                        break;
                    }
                    case 'dotfile':{
                        $return = substr($this->name, 0, 1) == '.';
                        break;
                    }
                    case 'parent' :{
                        if ($this->parent == null) {
                            $this->parent = new Directory('');
                        }
                        $return = $this->parent;
                        break;
                    }
                    case 'access' :
                        return $this->getSecurityObject();
                    default: {
                        break;
                    }
                }
                return $return;
            }

            function __set($property, $value) {
                switch ($property){
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
                return $this->attributes;
            }

            /**
             * Копирует директорию
             *
             * @param string $path
             * @return void
             */
            public function CopyTo($path){
                Directory::copy($this->path, $path);
            }

            /**
             * Перемещает директорию
             *
             * @param string $path
             * @return void
             */
            public function MoveTo($path){
                Directory::move($this->path, $path);
            }

            /**
             * Возвращает наименование директории
             *
             * @return string
             */
            public function ToString(){
                return $this->path;
            }

            /**
             * Проверяет директория ли
             *
             * @param string $path
             * @return boolean
             */
            static function IsDir($path) {
                try {
                    return substr($path, strlen($path) - 1, 1) == '/';
                }
                catch(\Exception $e) {
                    return false;
                }
            }

            /**
             * Проверяет есть ли директория на диске
             *
             * @param string $path
             * @return boolean
             */
            static function Exists($path){
                return File::Exists(dirname($path[strlen($path) - 1] == '/' ? $path . '#' : $path));
            }

            /**
             * Создает директорию
             *
             * @param string $path
             * @param boolean $recursive
             * @param integer $mode
             * @return Directory
             */
            static function Create($path, $recursive = true, $mode = 0777) {
                if(!Directory::Exists($path)) {
                    $path2 = dirname($path[strlen($path) - 1] == '/' ? $path . '#' : $path);
                    mkdir($path2, $mode, $recursive);
                    try {
                        chmod($path2, $mode);
                    }
                    catch(\Exception $e) {
                        
                    }
                }

                return new Directory($path);
            }

            /**
             * Удаляет директорию с диска
             *
             * @param string $path
             * @return void
             */
            static function Delete($path){

                if (!Directory::exists($path)) {
                    throw new AppException('directory not exists');
                }

                if (is_dir($path)) {
                    $objects = scandir($path);
                    foreach ($objects as $object) {
                        if ($object != '.' && $object != '..') {
                            if (is_dir($path."/".$object)) {
                                Directory::Delete($path.'/'.$object);
                            }
                            else {
                                unlink($path.'/'.$object);
                            }
                        }
                    }
                    rmdir($path);
                }

            }

            /**
             * Копирует директорию
             *
             * @param string $from
             * @param string $to
             * @return void
             */
            static function Copy($from, $to){
                if (!Directory::Exists($from)) {
                    throw new AppException('source directory not exists');
                }

                if (!Directory::Exists($to)) {
                    Directory::Create($to, true, 0766);
                }

                $dir = opendir($from);
                while(false !== ( $file = readdir($dir)) ) {
                    if (( $file != '.' ) && ( $file != '..' )) {
                        if ( is_dir($from . '/' . $file) ) {
                            Directory::Copy($from . '/' . $file . '/', $to . '/' . $file . '/');
                        }
                        else {
                            File::Copy($from . '/' . $file, $to . '/' . $file);
                        }
                    }
                }
                closedir($dir);

            }

            /**
             * Перемещает директорию
             *
             * @param string $from
             * @param string $to
             * @return void
             */
            static function Move($from, $to){
                if (!Directory::exists($from)) {
                    throw new AppException('source directory not exists');
                }
                if (Directory::exists($to)) {
                    throw new AppException('target directory exists');
                }

                rename($from, $to);
            }

            static function PathInfo($filename) {
                $pathInfo = [];
                $pathInfo['dirname'] = dirname($filename);
                $pathInfo['basename'] = trim(substr($filename, strlen($pathInfo['dirname']) + 1), '/');
                $parts = explode('.', $pathInfo['basename']);
                $pathInfo['extension'] = end($parts);
                $pathInfo['filename'] = substr($pathInfo['basename'], 0, -1 * strlen('.'.$pathInfo['extension']));
                return $pathInfo;
            }

            /**
             * Возвращает массив из данных
             *
             * @return void
             */
            public function ToArray() {
                return array(
                    'name' => $this->name,
                    'path' => $this->path.'/',
                    'created' => $this->getAttributesObject()->created,
                    'modified' => $this->getAttributesObject()->modified,
                    'lastaccess' => $this->getAttributesObject()->lastaccess,
                    /* get directory security */
                );
            }

        }

    }