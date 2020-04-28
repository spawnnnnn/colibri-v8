<?php

    namespace Colibri\Xml\Serialization {

        class XmlCData implements \JsonSerializable {

            public $value;
    
            public function __construct($value = null) {
                $this->value = $value;
            }
    
            public function jsonSerialize() {
                return (object)array('class' => self::class,'value' => $this->value);
            }
    
        }

    }