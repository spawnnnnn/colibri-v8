<?php
    /**
     * FileSystem
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\FileSystem
     */
    namespace Colibri\IO\FileSystem {

        /**
         * Класс для работы с файлами
         *
         * @property-read Attributes $attributes
         * @property-read string $filename
         * @property-read string $name
         * @property-read string $extension
         * @property-read Directory $directory
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
        class File extends Node
        {
            /**
             * Данные о пути к файлу
             *
             * @var array
             */
            private $info;

            /**
             * Длина файла в байтах
             *
             * @var integer
             */
            private $_size = 0;

            /** режим чтение */
            const MODE_READ = "rb9";
            /** режим запись */
            const MODE_WRITE = "wb9";
            /** режим добавление данных */
            const MODE_APPEND = "ab9";
            /** режим создания при записи */
            const MODE_CREATEWRITE = "wb9";

            /**
             * Конструктор
             *
             * @param string $path Путь к файлу
             */
            public function __construct($path)
            {
                $this->info = Directory::PathInfo($path);
                if ($this->info['basename'] == '') {
                    throw new Exception('path argument is not a file path');
                }

                if ($this->info['dirname'] == '.') {
                    $this->info['dirname'] = '';
                }
            }

            /**
             * Геттер
             *
             * @param string $property свойство
             * @return mixed
             */
            public function __get($property)
            {
                $return = null;
                switch (strtolower($property)) {
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
                        } else {
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
                        if ($this->_size == 0) {
                            $this->_size = filesize($this->path);
                        }
                        $return = $this->_size;
                        break;
                    }
                    case 'exists': {
                        $return = self::exists($this->path);
                        break;
                    }
                    case 'access': {
                        $return = $this->getSecurityObject();
                        break;
                    }
                    case 'content': {
                        if (self::exists($this->path)) {
                            $return = file_get_contents($this->path);
                        }
                        break;
                    }
                    default: {
                        if (strstr(strtolower($property), 'attr_') !== false) {
                            $p = str_replace('attr_', '', strtolower($property));
                            $return = $this->getAttributesObject()->$p;
                        }
                        break;
                    }

                }
                return $return;
            }

            /**
             * Копирует файл
             *
             * @param string $path путь, куда скопировать
             * @return void
             */
            public function CopyTo($path)
            {
                self::Copy($this->path, $path);
            }

            /**
             * Переместить файл
             *
             * @param string $path путь, куда переместить
             * @return void
             */
            public function MoveTo($path)
            {
                self::Move($this->path, $path);
            }

            /**
             * Возвращает имя файла
             *
             * @return string
             */
            public function ToString()
            {
                return $this->name;
            }

            /**
             * Считывает данные файл
             *
             * @param string $path путь к файлу
             * @return string
             */
            public static function Read($path)
            {
                if (self::Exists($path)) {
                    return file_get_contents($path);
                }
                return false;
            }
            
            /**
             * Записывает данные в файл
             *
             * @param string $path пусть к файлу
             * @param string $content контент, который нужно записать
             * @param boolean $recursive если true то папки будут созданы по всему пути до достижения $path
             * @param integer $mode режим создания файла и папок, по умолчанию 777
             * @return void
             */
            public static function Write($path, $content, $recursive = false, $mode = '777')
            {
                if (!self::Exists($path)) {
                    self::Create($path, $recursive, $mode);
                }

                file_put_contents($path, $content);
            }

            /**
             * Записывает данные в файл
             *
             * @param string $path путь к файлу
             * @param string $content данные, которые нужно дозаписать
             * @param boolean $recursive если true то папки будут созданы по всему пути до достижения $path
             * @param integer $mode режим создания файла и папок, по умолчанию 777
             * @return void
             */
            public static function Append($path, $content, $recursive = false, $mode = '777')
            {
                if (!self::Exists($path)) {
                    self::Create($path, $recursive, $mode);
                }

                file_put_contents($path, $content, FILE_APPEND);
            }

            /**
             * Возвращает стрим файла
             *
             * @param string $path путь к файлу
             * @return FileStream
             */
            public static function Open($path)
            { //ireader
                if (self::Exists($path)) {
                    return new FileStream($path);
                }
                return false;
            }

            /**
             * Проверяет наличие файла
             *
             * @param string $path путь к файлу
             * @return boolean
             */
            public static function Exists($path)
            {
                return file_exists($path);
            }
            
            /**
             * Проверяет пустой ли файл
             *
             * @param string $path путь к файлу
             * @return boolean
             */
            public static function IsEmpty($path)
            {
                try { 
                    $info = stat($path);
                    return $info['size'] == 0;
                } catch (Exception $e) {
                    return true;
                }
            }

            /**
             * Создает файл и возвращает стрим
             *
             * @param string $path путь к файлу
             * @param boolean $recursive если true то папки будут созданы по всему пути до достижения $path
             * @param string $mode режим создания файла и папок, по умолчанию 777
             * @return FileStream
             */
            public static function Create($path, $recursive = true, $mode = '777')
            {
                if (!Directory::Exists($path) && $recursive) {
                    Directory::Create($path, $recursive, $mode);
                }

                if (!self::Exists($path)) {
                    touch($path);
                }

                return new FileStream($path);
            }

            /**
             * Удаляет файл
             *
             * @param string $path путь к файлу
             * @return boolean
             */
            public static function Delete($path)
            {
                if (!self::Exists($path)) {
                    throw new Exception('file not exists');
                }

                return unlink($path);
            }

            /**
             * Копирует файла
             *
             * @param string $from какой файл
             * @param string $to куда скопировать
             * @return void
             */
            public static function Copy($from, $to)
            {
                if (!self::Exists($from)) {
                    throw new Exception('file not exists');
                }

                copy($from, $to);
            }

            /**
             * Переносит файл
             *
             * @param string $from какой файл
             * @param string $to куда перенести
             * @return void
             */
            public static function Move($from, $to)
            {
                if (!self::Exists($from)) {
                    throw new Exception('source file not exists');
                }

                rename($from, $to);
            }

            /**
             * Проверяет не директория ли
             *
             * @param string $path путь к файлу или директории
             * @return boolean
             */
            public static function IsDirectory($path)
            {
                return is_dir($path);
            }

            /**
             * Возвращает данные в виде массива
             *
             * @return array
             */
            public function ToArray()
            {
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
