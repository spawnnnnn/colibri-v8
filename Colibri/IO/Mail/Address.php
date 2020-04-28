<?php
    /**
     * Mail
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Mail
     */
    namespace Colibri\IO\Mail {

        use Colibri\Helpers\Strings;
        use Colibri\Helpers\Variable;

        /**
         * Электронный адрес
         */
        class Address
        {
            private $_address;
            private $_displayName;
            private $_charset;
            
            public function __construct($address, $displayName = '', $charset = 'utf-8')
            {
                $address = trim($address);
                $displayName = trim(preg_replace('/[\r\n]+/', '', $displayName)); //Strip breaks and trim
                
                $this->_address = $address;
                $this->_displayName = $displayName;
                $this->_charset = $charset;
            }
            
            public function __get($property)
            {
                $return = null;
                switch (strtolower($property)) {
                    case 'address':{
                        $return =  $this->_address;
                        break;
                    }
                    case 'name':{
                        $return =  $this->_displayName;
                        break;
                    }
                    case 'charset':{
                        $return =  $this->_charset;
                        break;
                    }
                    case 'formated':{
                        $return =  $this->_formatAddress();
                        break;
                    }
                    default: {
                        break;
                    }
                }
                return $return;
            }

            public function __set($property, $value)
            {
                switch (strtolower($property)) {
                    case 'address':{
                        $this->_address = trim($value);
                        break;
                    }
                    case 'name':{
                        $this->_displayName = trim(preg_replace('/[\r\n]+/', '', $value));
                        break;
                    }
                    case 'charset':{
                        $this->_charset = $value;
                        break;
                    }
                    default: {
                        break;
                    }
                }
            }
            
            private function _formatAddress()
            {
                if (Variable::IsEmpty($this->_displayName)) {
                    return Helper::StripNewLines($this->_address);
                } else {
                    return Helper::EncodeHeader(Helper::StripNewLines($this->_displayName), 'phrase', $this->_charset) . ' <' . Helper::StripNewLines($this->_address) . '>';
                }
            }
        }

    }
