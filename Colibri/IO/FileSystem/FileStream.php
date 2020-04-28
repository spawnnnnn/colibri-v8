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
         * Работа с стримом файла
         */
        class FileStream extends Stream
        {

            /**
             * Виртуальный файл
             *
             * @var boolean
             */
            protected $_virtual;

            /**
             * Конструктор
             *
             * @param string $source
             * @param boolean $virtual
             */
            public function __construct($source, $virtual = false)
            {
                $this->_virtual = $virtual;
                $this->_stream = fopen($source, "rw+");
                if (!$this->_virtual) {
                    $this->_length = filesize($source);
                } else {
                    $this->_length = -1;
                }
            }

            /**
             * Передвинуть позицию
             *
             * @param integer $offset
             * @return void
             */
            public function seek($offset = 0)
            {
                if ($offset == 0) {
                    return;
                }

                fseek($this->_stream, $offset);
            }

            /**
             * Считать из файла
             *
             * @param integer $offset
             * @param integer $count
             * @return string
             */
            public function read($offset = 0, $count = 0)
            {
                $this->seek($offset);
                return fread($this->_stream, $count);
            }

            /**
             * Записaть в файл
             *
             * @param string $buffer
             * @param integer $offset
             * @return void
             */
            public function write($buffer, $offset = 0)
            {
                $this->seek($offset);
                return fwrite($this->_stream, $buffer);
            }

            /**
             * Сбросить изменения на диск
             *
             * @return void
             */
            public function flush()
            {
                fflush($this->_stream);
            }

            /**
             * Закрыть дескриптор
             *
             * @return void
             */
            public function close()
            {
                $this->flush();
                fclose($this->_stream);
                $this->_stream = false;
            }

            public function __get($property)
            {
                if ($property == 'stream') {
                    return $this->_stream;
                }
                return null;
            }
        }

    }
