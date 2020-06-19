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
             * @param string $name название свойства
             * @param string $filePathOrFileData данные или путь к файлу
             * @param string $filename название файла
             * @param boolean $mime тип MIME
             */
            public function __construct($name, $filePathOrFileData, $filename = '', $mime = false)
            {

                // $data is file path
                if (File::Exists($filePathOrFileData)) {
                    $fi = new File($filePathOrFileData);
                    $filename = $fi->name;
                    $filePathOrFileData = File::Read($filePathOrFileData);
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
