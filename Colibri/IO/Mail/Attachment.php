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
            public $path; // file path or string (if attachment is string type)
            public $filename; // file name
            public $charset; // file encoding
            public $encoding; // file encoding
            public $name; // attachment name
            public $type; // mime type
            public $cid; // ??
            public $isString; // is attachemtn is string
            public $isInline; // is inline image
            
            public function __construct()
            {
            }
            
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
