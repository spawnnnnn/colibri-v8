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
        class AttachmentList extends ArrayList
        {
            /**
             * Добавить вложение в список
             *
             * @param Attachment $a вложение
             * @return void
             */
            public function Add($a)
            {
                if (!($a instanceof Attachment)) {
                    throw new Exception(ErrorMessages::InvalidArgument);
                }
                    
                parent::Add($a);
            }
            
            /**
             * Добавить список вложений в текущий список
             *
             * @param Attachment[] $values
             * @return void
             */
            public function AddRange($values)
            {
                foreach ($values as $v) {
                    if (!($v instanceof Attachment)) {
                        throw new Exception(ErrorMessages::InvalidArgument);
                    }
                }
                parent::Append($values);
            }

            /**
             * Отвечает true если в списке есть встроенные вложения
             *
             * @return bool
             */
            public function HasInline()
            {
                foreach ($this as $a) {
                    if ($a->isInline) {
                        return true;
                    }
                }
                return false;
            }
        }

    }
