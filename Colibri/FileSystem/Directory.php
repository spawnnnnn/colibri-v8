<?php
    /**
     * FileSystem
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\FileSystem
     */
    namespace Colibri\FileSystem {

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
        class Directory extends Node
        {
            private $path;
            private $parent;
            private $pathArray;

            /**
             * Конструктор
             *
             * @param string $path
             */
            public function __construct($path)
            {
                $this->path = dirname($path[strlen($path) - 1] == '/' ? $path . '#' : $path);
            }

            public function __get($property)
            {
                $return = null;
                switch (strtolower($property)) {
                    case 'current':
                    case 'size': {
                        $return = null;
                        break;
                    }
                    case 'attributes':{
                        $return = $this->getAttributesObject();
                        break;
                    }
                    case 'name':{
                        if (!$this->pathArray) {
                            $this->pathArray = explode('/', $this->path);
                        }
                        $return = $this->pathArray[count($this->pathArray) - 1];
                        break;
                    }
                    case 'path':{
                        $return = $this->path.'/';
                        break;
                    }
                    case 'dotfile':{
                        $return = substr($this->name, 0, 1) == '.';
                        break;
                    }
                    case 'parent':{
                        if ($this->parent == null) {
                            $this->parent = new Directory('');
                        }
                        $return = $this->parent;
                        break;
                    }
                    case 'access':
                        return $this->getSecurityObject();
                    default: {
                        break;
                    }
                }
                return $return;
            }

            /**
             * Копирует директорию
             *
             * @param string $path
             * @return void
             */
            public function CopyTo($path)
            {
                self::Copy($this->path, $path);
            }

            /**
             * Перемещает директорию
             *
             * @param string $path
             * @return void
             */
            public function MoveTo($path)
            {
                self::Move($this->path, $path);
            }

            /**
             * Возвращает наименование директории
             *
             * @return string
             */
            public function ToString()
            {
                return $this->path;
            }

            /**
             * Проверяет директория ли
             *
             * @param string $path
             * @return boolean
             */
            public static function IsDir($path)
            {
                try {
                    return substr($path, strlen($path) - 1, 1) == '/';
                } catch (Exception $e) {
                    return false;
                }
            }

            /**
             * Проверяет есть ли директория на диске
             *
             * @param string $path
             * @return boolean
             */
            public static function Exists($path)
            {
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
            public static function Create($path, $recursive = true, $mode = 0777)
            {
                if (!self::Exists($path)) {
                    $path2 = dirname($path[strlen($path) - 1] == '/' ? $path . '#' : $path);
                    mkdir($path2, $mode, $recursive);
                    try {
                        chmod($path2, $mode);
                    } catch (Exception $e) {
                    }
                }

                return new self($path);
            }

            /**
             * Удаляет директорию с диска
             *
             * @param string $path
             * @return void
             */
            public static function Delete($path)
            {
                if (!self::Exists($path)) {
                    throw new Exception('directory not exists');
                }

                if (is_dir($path)) {
                    $objects = scandir($path);
                    foreach ($objects as $object) {
                        if ($object != '.' && $object != '..') {
                            if (is_dir($path."/".$object)) {
                                self::Delete($path.'/'.$object);
                            } else {
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
            public static function Copy($from, $to)
            {
                if (!self::Exists($from)) {
                    throw new Exception('source directory not exists');
                }

                if (!self::Exists($to)) {
                    self::Create($to, true, 0766);
                }

                $dir = opendir($from);
                while (false !== ($file = readdir($dir))) {
                    if (($file != '.') && ($file != '..')) {
                        if (is_dir($from . '/' . $file)) {
                            self::Copy($from . '/' . $file . '/', $to . '/' . $file . '/');
                        } else {
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
            public static function Move($from, $to)
            {
                if (!self::exists($from)) {
                    throw new Exception('source directory not exists');
                }
                if (self::exists($to)) {
                    throw new Exception('target directory exists');
                }

                rename($from, $to);
            }

            public static function PathInfo($filename)
            {
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
            public function ToArray()
            {
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
