<?php

    /**
     * Класс описывающий файл отправленный в запрос
     * Только для чтения
     * 
     * @author Ваган Григорян <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Web
     * @version 1.0.0
     * 
     */
    namespace Colibri\Web {
        
        /**
         * Файл из списка файлов запроса
         * 
         * @property boolean $isValid
         * @property string $binary
         * 
         */
        class RequestedFile {

            /**
             * Название файла
             *
             * @var string
             */
            public $name;
            /**
             * Расширение файла
             *
             * @var string
             */
            public $ext;
            /**
             * Тип файла
             *
             * @var string
             */
            public $mimetype;
            /**
             * Ошибка
             *
             * @var string
             */
            public $error;
            /**
             * Размер файла в байтах
             *
             * @var int
             */
            public $size;
            /**
             * Пусть к временному файлу
             *
             * @var string
             */
            public $temporary;

            /**
             * Конструктор
             *
             * @param array $arrFILE
             */
            function __construct($arrFILE) {

                if(!$arrFILE) {
                    return;
                }

                $this->name = $arrFILE["name"];
                $ret = preg_split("/\./i", $this->name);
                if(count($ret) > 1 ) {
                    $this->ext = $ret[count($ret) - 1];
                }
                $this->mimetype = $arrFILE["type"];
                $this->temporary = $arrFILE["tmp_name"];
                $this->error = $arrFILE["error"];
                $this->size = $arrFILE["size"];

            }

            /**
             * Магический метод
             *
             * @param string $prop
             * @return mixed
             */
            public function __get($prop) {
                $prop = strtolower($prop);
                if($prop == 'isvalid') {
                    return !empty($this->name);
                }
                else if($prop == 'binary') {
                    return file_get_contents($this->temporary);
                }
                return null;
            }

            /**
             * Удаление класса
             */
            function __destruct(){
                if(file_exists($this->temporary)){
                    unlink($this->temporary);
                }
            }

            /**
             * Сохраняет временый файл в указанную директорую
             *
             * @param string $path
             * @return void
             */
            function MoveTo($path) {
                rename($this->temporary, $path);
            }

        }
    }