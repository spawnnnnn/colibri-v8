<?php

    namespace Colibri\Graphics {
            
        /**
         * Класс представляющий точку на экране
         */
        class Point {

            /**
             * Позиция X
             *
             * @var int
             */
            public $x;
            /**
             * Позиция Y
             *
             * @var int
             */
            public $y;
            
            /**
             * Конструктор
             *
             * @param integer $x
             * @param integer $y
             */
            public function __construct($x = 0, $y = 0) {
                $this->x = $x;
                $this->y = $y;
            }
            
        }

    }