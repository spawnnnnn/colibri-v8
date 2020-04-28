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
         * Список прикреплений
         */
        class AttachmentList extends ArrayList {
            

            public function Add($a) {
                if(!($a instanceOf Attachment)) {
                    throw new Exception(ErrorMessages::InvalidArgument);
                }
                    
                parent::Add($a);
            }
            
            public function AddRange($values) {
                foreach($values as $v) {
                    if(!($v instanceOf Attachment)){
                        throw new Exception(ErrorMessages::InvalidArgument);
                    }
                }
                parent::Append($values);
            }

            public function HasInline() {
                foreach($this as $a) { 
                    if($a->isInline) {
                        return true;
                    }
                }
                return false;
            }        
            
        }

    }