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
         * Класс полученного письма
         * 
         * @see https://github.com/barbushin/php-imap
         * @author Barbushin Sergey http://linkedin.com/in/barbushin
         */
        class Mail {

            /**
             * ID письма
             *
             * @var string
             */
            public $id;

            /**
             * Дата получения письма
             *
             * @var string
             */
            public $date;

            /**
             * Заголовки в исходном виде
             *
             * @var string
             */
            public $headersRaw;

            /**
             * Заголовоки
             *
             * @var string[]
             */
            public $headers;

            /**
             * Тема письма
             *
             * @var string
             */
            public $subject;

            /**
             * Имя отправителя
             *
             * @var string
             */
            public $fromName;

            /**
             * Адрес отправителя
             *
             * @var string
             */
            public $fromAddress;

            /**
             * Кому было адресовано письмо
             *
             * @var string[]
             */
            public $to = array();

            /**
             * Кому было адресовано письмйоа в виде текста
             *
             * @var string
             */
            public $toString;

            /**
             * CC еще кому было отправлено письмо
             *
             * @var string[]
             */
            public $cc = array();

            /**
             * BCC
             *
             * @var string[]
             */
            public $bcc = array();

            /**
             * ReplayTo
             *
             * @var string[]
             */
            public $replyTo = array();

            /**
             * ID сообщения
             *
             * @var string
             */
            public $messageId;

            /**
             * В виде обычного текста
             *
             * @var string
             */
            public $textPlain;

            /**
             * В виде HTML
             *
             * @var string
             */
            public $textHtml;
            
            /**
             * Вложения
             * 
             * @var Mail[] 
             */
            protected $attachments = array();

            /**
             * ДОбавить вложение
             *
             * @param Mail $attachment
             * @return void
             */
            public function addAttachment(Mail $attachment) {
                $this->attachments[$attachment->id] = $attachment;
            }
            /**
             * Возвращает все вложения
             * @return Mail[]
             */
            public function getAttachments() {
                return $this->attachments;
            }
            /**
             * Get array of internal HTML links placeholders
             * @return array attachmentId => link placeholder
             */
            public function getInternalLinksPlaceholders() {
                return preg_match_all('/=["\'](ci?d:([\w\.%*@-]+))["\']/i', $this->textHtml, $matches) ? array_combine($matches[2], $matches[1]) : array();
            }
            /**
             * Заменяет вложенные ссылки
             *
             * @param string $baseUri
             * @return string
             */
            public function replaceInternalLinks($baseUri) {
                $baseUri = rtrim($baseUri, '\\/') . '/';
                $fetchedHtml = $this->textHtml;
                foreach($this->getInternalLinksPlaceholders() as $attachmentId => $placeholder) {
                    if(isset($this->attachments[$attachmentId])) {
                        $fetchedHtml = str_replace($placeholder, $baseUri . basename($this->attachments[$attachmentId]->filePath), $fetchedHtml);
                    }
                }
                return $fetchedHtml;
            }
        }

    }