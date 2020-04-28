<?php
    /**
     * Mail
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Mail
     */
    namespace Colibri\IO\Mail {

        use Colibri\App;
        use Colibri\Xml\XmlNode;

        /**
         * Шаблон письма
         */
        class Template {
            
            private $_data;
            
            public function __construct($file, $isFile = true) {
                if($isFile) {
                    ob_start();
                    require($file);
                    $this->_data = ob_get_contents();
                    ob_end_clean();
                }
                else{
                    $this->_data = $file;
                }

            }
            
            public static function Create($file, $isFile = true) {
                return new Template($file, $isFile);
            }
            
            public static function ObjectToReplacements($object, $startKey = 'item') {
                $replacements = array();
                foreach($object as $key => $value) {
                    if(is_object($value)) {
                        $replacements = array_merge($replacements, Template::ObjectToReplacements($value, $startKey.'.'.$key));
                    }
                    else if(is_array($value)) {
                        foreach($value as $index => $vvv) {
                            $replacements[':'.$startKey.'['.$index.']:'] = $vvv;
                        }
                    }
                    else{
                        $replacements[':'.$startKey.'.'.$key.':'] = $value;
                    }
                }
                return $replacements;
            }
            
            public function Apply($replacements) {
                foreach($replacements as $key => $value) {
                    $this->_data = str_replace($key, $value, $this->_data);
                }
                if ($this->_data) {
                    $xml = XmlNode::LoadHTML('<'.'?xml version="1.0" encoding="utf-8" ?'.'>'.$this->_data, false, 'utf-8');
                    foreach($xml->Query('//img') as $image) {
                        if($image->attributes->src && strstr($image->attributes->src->value, '://') === false){
                            $image->attributes->src->value = 'https://'.App::$request->server->server_name.$image->attributes->src->value;  
                        }     
                    }
                    
                    foreach($xml->Query('//iframe') as $iframe) {
                        if($iframe->attributes->src && strstr($iframe->attributes->src->value, '://') === false){
                            $iframe->attributes->src->value = 'https://'.App::$request->server->server_name.$iframe->attributes->src->value;
                        }       
                    }

                    foreach($xml->Query('//a') as $a)                 {
                        if($a->attributes->href && strstr($a->attributes->href->value, '://') === false){
                            $a->attributes->href->value = 'https://'.App::$request->server->server_name.$a->attributes->href->value;   
                        }             
                    }
                    
                    $this->_data = $xml->body->html;
                    $this->_data = str_replace('<body>', '', str_replace('</body>', '', $this->_data));
                }
                return $this;
            }
            
            public function ToString($replacements = false) {
                if($replacements){
                    $this->Apply($replacements);
                }
                return $this->_data;
            }
            
        }

    }