<?php
    /**
     * Mailbox
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Mail\Mailbox
     */
    namespace Colibri\IO\Mail\Mailbox {

        /**
         * Данные о вложении
         */
        class Mail {
            /**
             * ID вложения
             *
             * @var string
             */
            public $id;

            /**
             * Название вложения
             *
             * @var string
             */
            public $name;

            /**
             * Путь к файлу
             *
             * @var string
             */
            public $filePath;

            /**
             * Строка Disposition для вложения
             *
             * @var string
             */
            public $disposition;
        }

    }