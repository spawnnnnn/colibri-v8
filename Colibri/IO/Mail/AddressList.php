<?php
    /**
     * Mail
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Mail
     */
    namespace Colibri\IO\Mail {

        use Colibri\Collections\ArrayList;

        /**
         * Список электронных адресов
         */
        class AddressList extends ArrayList
        {
            public function Add($a)
            {
                if (!($a instanceof Address)) {
                    throw new Exception(ErrorMessages::InvalidArgument);
                }
                parent::Add($a);
            }
            
            public function AddRange($values)
            {
                foreach ($values as $v) {
                    if (!($v instanceof Address)) {
                        throw new Exception(ErrorMessages::InvalidAddress);
                    }
                }
                parent::Append($values);
            }
            
            public function Join()
            {
                $ret = '';
                foreach ($this as $a) {
                    $ret .= ', '.$a->formated;
                }
                return substr($ret, strlen(', '));
            }
        }
    }
