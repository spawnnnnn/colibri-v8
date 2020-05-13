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
         * @see https://github.com/barbushin/php-imap
         * @author Barbushin Sergey http://linkedin.com/in/barbushin
         */
        class Mail {
            public $id;
            public $date;
            public $headersRaw;
            public $headers;
            public $subject;
            public $fromName;
            public $fromAddress;
            public $to = array();
            public $toString;
            public $cc = array();
            public $bcc = array();
            public $replyTo = array();
            public $messageId;
            public $textPlain;
            public $textHtml;
            
            /** @var Mail[] */
            protected $attachments = array();
            public function addAttachment(Mail $attachment) {
                $this->attachments[$attachment->id] = $attachment;
            }
            /**
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