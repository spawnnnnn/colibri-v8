<?php
    /**
     * Mail
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Mail
     */
    namespace Colibri\IO\Mail {

        use Colibri\IO\FileSystem\File;

        /**
         * Прикрепление
         */
        class Attachment
        {
            /**
             * Путь к файлу
             *
             * @var string
             */
            public $path; 

            /**
             * Наименование файла
             *
             * @var string
             */
            public $filename;
            
            /**
             * Кодировка файла
             *
             * @var string
             */
            public $charset;

            /**
             * Кодировка данных вложения
             *
             * @var string
             */
            public $encoding; 
            
            /**
             * Название прикрепления
             *
             * @var string
             */
            public $name;

            /**
             * Тип MIME для файла
             *
             * @var string
             */
            public $type; 

            /**
             * Черт его знает что это
             * 
             * @var string
             */
            public $cid;

            /**
             * true если файл это строка
             *
             * @var bool
             */
            public $isString;

            /**
             * true если впложение inline
             *
             * @var bool
             */
            public $isInline;
            
            /**
             * Конструктор
             */
            public function __construct()
            {
            }
            
            /**
             * Создает вложение
             *
             * @param string $path путь к файлу
             * @param string $name название вложения
             * @param string $encoding кодировка данных вложения
             * @param string $type MIME тип вложения
             * @param string $charset кодировка вложения
             * @return Attachment
             */
            public static function Create($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream', $charset = '')
            {
                $ma = new Attachment();

                if (!File::Exists($path)) {
                    throw new Exception(ErrorMessages::FileAccess.$path);
                }
                
                $filename = basename($path);
                if ($name == '') {
                    $name = $filename;
                }

                $ma->path = $path;
                $ma->filename = $filename;
                $ma->charset = $charset;
                $ma->name = $name;
                $ma->encoding = $encoding;
                $ma->type = $type;
                $ma->cid = 0;
                $ma->isString = false;
                $ma->isInline = false;
                        
                return $ma;
            }
            
            /**
             * Создает строковое вложение
             *
             * @param string $string данные вложения
             * @param string $filename название файла
             * @param string $encoding кодировка данных вложения
             * @param string $type MIME тип вложения
             * @param string $charset кодировка вложения
             * @return Attachment
             */
            public static function CreateString($string, $filename, $encoding = 'base64', $type = 'application/octet-stream', $charset = '')
            {
                $ma = new Attachment();
                
                $ma->path = $string;
                $ma->filename = $filename;
                $ma->charset = $charset;
                $ma->name = basename($filename);
                $ma->encoding = $encoding;
                $ma->type = $type;
                $ma->cid = 0;
                $ma->isString = true;
                $ma->isInline = false;
                
                return $ma;
            }
            
            /**
             * Создает встроенное вложение
             *
             * @param string $path путь к файлу
             * @param string $cid не знаю что это
             * @param string $name название вложения
             * @param string $encoding кодировка данных вложения
             * @param string $type MIME тип вложения
             * @param string $charset кодировка вложения
             * @return void
             */
            public function CreateEmbeded($path, $cid, $name = '', $encoding = 'base64', $type = 'application/octet-stream', $charset = '')
            {
                $ma = new Attachment();
                
                if (!File::Exists($path)) {
                    throw new Exception(ErrorMessages::FileAccess.$path);
                }

                $_filename = basename($path);
                if ($name == '') {
                    $name = $_filename;
                }

                $ma->path = $path;
                $ma->filename = $_filename;
                $ma->charset = $charset;
                $ma->name = $name;
                $ma->encoding = $encoding;
                $ma->type = $type;
                $ma->cid = $cid;
                $ma->isString = false;
                $ma->isInline = true;

                return $ma;
            }
        }
    }
