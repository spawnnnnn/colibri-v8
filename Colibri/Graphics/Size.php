<?php

    /**
     * Graphics
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Graphics
     */
    namespace Colibri\Graphics {

        /**
         * Класс представляющий размер
         * 
         * @property-read string $style вернуть текст для аттрибута style
         * @property-read string $attributes вернуть текст в виде аттрибутов
         * @property-read string $params вернуть в виде параметров к запросу
         * @property-read bool $isNull пустой ли
         * 
         */
        class Size {

            /**
             * Ширина
             *
             * @var int
             */
            public $width;
            /**
             * Высота
             *
             * @var int
             */
            public $height;

            /**
             * Конструктор
             *
             * @param integer $width
             * @param integer $height
             */
            public function __construct($width = 0, $height = 0) {
                $this->width = $width;
                $this->height = $height;
            }
            
            /**
             * Геттер
             *
             * @param string $nm
             * @return mixed
             */
            public function __get($nm) {
                $return = null;
                switch($nm) {
                    case "style": {
                        $return = ($this->width != 0 ? "width:".intval($this->width)."px;" : "").($this->height != 0 ? "height:".intval($this->height)."px;" : "");
                        break;
                    }
                    case "attributes": {
                        $return = ($this->width != 0 ? " width=\"".intval($this->width)."\"" : "").($this->height != 0 ? " height=\"".intval($this->height)."\"" : "");
                        break;
                    }
                    case "params":{
                        $return = ($this->width != 0 ? "&w=".intval($this->width) : "").($this->height != 0 ? "&h=".intval($this->height) : "");
                        break;
                    }
                    case "isNull": {
                        $return = ($this->width == 0 && $this->height == 0);
                        break;
                    }
                    default: {
                        break;
                    }
                }
                return $return;
            }
            
            /**
             * Трансформировать размер
             *
             * @param Size $size
             * @return Size
             */
            public function TransformTo($size) {
                
                $_width = $size->width;
                $_height = $size->height;

                if($_width == 0 && $_height == 0) {
                    return new Size(0, 0);
                }
                else if($_width == 0) {
                    $_height = $_height <= $this->height ? $_height : $this->height;
                    $_width = $_height / ($this->height / $this->width);
                }
                else if($_height == 0) {
                    $_width = ($_width <= $this->width ? $_width : $this->width);
                    $_height = $_width / ($this->width / $this->height);
                }
                else if($this->width <= $_width && $this->height <= $_height) {
                    $_width = $this->width;
                    $_height = $this->height;
                }
                else if($this->width / $_width > $this->height / $_height) {
                    $_height = $this->height * ($_width / $this->width);
                }
                else {
                    $_width = $this->width * ($_height / $this->height);
                }
                
                return new Size($_width, $_height);

            }
            
            /**
             * Трансформирует размер так, чтобы реальный размер покрывал область изменяемого
             *
             * @param Size $size
             * @return Size
             */
            public function TransformToFill($size) {

                $_width = $size->width;
                $_height = $size->height;
                
                if($_width == 0 && $_height == 0) { 
                    return new Size(0, 0);
                }
                else if($_width == 0) {
                    $_height = ($_height <= $this->height ? $_height : $this->height);
                    $_width = $_height / ($this->height / $this->width);
                }
                else if($_height == 0) {
                    $_width = ($_width <= $this->width ? $_width : $this->width);
                    $_height = $_width / ($this->width / $this->height);
                }
                else if($this->width <= $_width && $this->height <= $_height) {
                    $_width = $this->width;
                    $_height = $this->height;
                }
                else if($this->width / $_width > $this->height / $_height) {
                    $_width = $this->width * ($_height / $this->height);
                }
                else {
                    $_height = $this->height * ($_width / $this->width);
                }
                
                return new Size($_width, $_height);
            }
            
            /**
             * Раздвигает размер
             *
             * @param int $w
             * @param int $h
             * @return void
             */
            public function Expand($w, $h) {
                $this->width += $w;
                $this->height += $h;
            }    
        }

    }