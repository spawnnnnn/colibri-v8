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
         * Базовый класс для File и Directory
         */
        class Node {

            protected $attributes;
            protected $access;

            public function __set($property, $value)
            {
                switch (strtolower($property)) {
                    case 'created':
                        $this->getAttributesObject()->created = $value;
                        break;
                    case 'modified':
                        $this->getAttributesObject()->modified = $value;
                        break;
                    case 'readonly':
                        $this->getAttributesObject()->readonly = $value;
                        break;
                    case 'hidden':
                        $this->getAttributesObject()->hidden = $value;
                        break;
                    default: {
                        break;
                    }
                }
            }
            
            protected function getAttributesObject()
            {
                if ($this->attributes === null) {
                    $this->attributes = new Attributes($this);
                }
                return $this->attributes;
            }
            
            protected function getSecurityObject()
            {
                if ($this->access === null) {
                    $this->access = new Security($this);
                }
                return $this->access;
            }

            
        }

    }