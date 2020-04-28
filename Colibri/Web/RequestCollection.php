<?php
    /**
     * Коллекция данных из запроса
     * Только для чтения
     * 
     * @author Ваган Григорян <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Web
     * @version 1.0.0
     * 
     */
    namespace Colibri\Web {

        use Colibri\Collections\ReadonlyCollection;

        /**
         * Коллекция данных из запроса
         * Readonly
         * 
         * Внимание! В целях избавления от проблемы XSS все данные слешируются посредством функции addslashes
         * 
         */
        class RequestCollection extends ReadonlyCollection {

            public function __construct($data = array(), $mq = null) {
                parent::__construct($data);
            }

            /**
             * Чистит или добавляет слэши в значения
             *
             * @param string | string[] $obj
             * @return string | string[]
             */
            private function _stripSlashes($obj) {
                if (is_array($obj)) {
                    foreach($obj as $k => $v) {
                        $obj[$k] = $this->_stripSlashes($v);
                    }
                    return $obj;
                } else {
                    return addslashes($obj);
                }
            }

            /**
             * Магический метод
             *
             * @param string $property
             * @return mixed
             */
            public function __get($property) {
                $val = parent::__get($property);
                return $this->_stripSlashes($val);
            }

        }

    }