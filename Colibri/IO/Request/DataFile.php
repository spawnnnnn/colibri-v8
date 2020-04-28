<?php
    /**
     * Request
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Request
     */
    namespace Colibri\IO\Request {

        use Colibri\Helpers\MimeType;
        use Colibri\IO\FileSystem\File;

        /**
         * Файл в запросе
         * @property string $name
         * @property string $mime
         * @property string $file
         * @property string $value
         */
        class DataFile extends DataItem
        {

            /**
             * Конструктор
             *
             * @param string $name
             * @param string $data
             * @return void
             */
            public function __construct($name, $filePathOrFileData, $filename = '', $mime = false)
            {

                // $data is file path
                if (File::Exists($filePathOrFileData)) {
                    $fi = new File($filePathOrFileData);
                    $filename = $fi->name;
                }

                parent::__construct($name, $filePathOrFileData);

                if (!$mime) {
                    $mime = MimeType::Create($filename)->data;
                }

                $this->mime = $mime;
                $this->file = $filename;
            }
        }

    }
