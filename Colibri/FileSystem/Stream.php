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
         * Абстрактный класс Стриминга
         */
        abstract class Stream
        {

            /**
             * Длина стрима
             *
             * @var integer
             */
            protected $_length = 0;
            /**
             * Декриптор
             *
             * @var mixed
             */
            protected $_stream;

            /**
             * Конструктор
             */
            public function __construct()
            {
            }

            /**
             * Деструктор
             */
            public function __destruct()
            {
                if ($this->_stream) {
                    $this->close();
                }
                unset($this->_stream);
            }

            public function __get(/*string*/ $property)
            {
                if ($property == 'length') {
                    return $this->_length;
                }
            }

            /**
             * Передвинуть позицию
             *
             * @param integer $offset
             * @return void
             */
            abstract public function seek($offset = 0);
            
            /**
             * Считать из стрима
             *
             * @param int $offset
             * @param int $count
             * @return void
             */
            abstract public function read($offset = null, $count = null);
            
            /**
             * Записать в стрим
             *
             * @param strimg $content
             * @param int $offset
             * @return void
             */
            abstract public function write($content, $offset = null); 

            /**
             * Сохранить изменения
             *
             * @return void
             */
            abstract public function flush();

            /**
             * Закрыть стрим
             *
             * @return void
             */
            abstract public function close(); 
        }

    }
