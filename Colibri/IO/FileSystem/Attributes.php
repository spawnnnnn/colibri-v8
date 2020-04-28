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
         * Аттрибуты файловой системы
         *
         * @property int $created
         * @property int $modified
         * @property int $lastaccess
         *
         */
        class Attributes
        {
            protected $source;
            protected $attributes = array();

            public function __construct($source)
            {
                $this->source = $source;
            }

            public function __get($property)
            {
                $return = null;
                switch ($property) {
                    case 'created':{
                        if (!array_key_exists('created', $this->attributes)) {
                            $this->attributes['created'] = filectime($this->source->path);
                        }

                        $return = $this->attributes['created'];
                        break;
                    }
                    case 'modified':{
                        if (!array_key_exists('created', $this->attributes)) {
                            $this->attributes['created'] = filemtime($this->source->path);
                        }

                        $return =  $this->attributes['created'];
                        break;
                    }
                    case 'lastaccess':{
                        if (!array_key_exists('created', $this->attributes)) {
                            $this->attributes['created'] = fileatime($this->source->path);
                        }

                        $return =  $this->attributes['created'];
                        break;
                    }
                    default:
                        if (array_key_exists($property, $this->attributes)) {
                            $return = $this->attributes->$property;
                        }
                }
                return $return;
            }

            public function __set($property, $value)
            {
                if (array_key_exists($property, $this->attributes)) {
                    $this->update($property, $value);
                }
            }

            private function update(/* $property, $value */)
            {
                //update every time on set new value -> С‚.Рє.
            }
        }

    }
