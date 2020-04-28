<?php
    /**
     * Mail
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Mail
     */
    namespace Colibri\IO\Mail {

        use Colibri\Helpers\MimeType;
        use Colibri\Helpers\Variable;

        /**
         * Письмо
         */
        class Message
        {
            public $id;
            public $priority           = 3;
            public $charset            = 'iso-8859-1';
            public $contenttype        = 'text/plain';
            public $errorinfo          = '';

            public $encoding           = '8bit';
            public $from               = null; // mailaddress
            public $subject            = '';
            public $body               = '';
            public $altbody            = '';
            public $wordwrap           = 0;
            public $confirmreadingto   = '';
            public $returnpath         = '';
            
            public $to                 = null; // array of mailaddress
            public $cc                 = null; // array of mailaddress
            public $bcc                = null; // array of mailaddress
            public $replyto            = null; // array of mailaddress
            
            public $attachments         = null;
            
            public $customheader       = array();
            
            public function __construct($from = null, $to = null, $subject = '')
            {
                $this->to = new AddressList();
                $this->cc = new AddressList();
                $this->bcc = new AddressList();
                $this->replyto = new AddressList();
                
                if (!Variable::IsNull($from)) {
                    if ($from instanceof Address) {
                        $this->from = $from;
                    } else {
                        // распарсить строку, если нужно
                        $this->from = new Address($from);
                    }
                }
                
                if (!Variable::IsNull($to)) {
                    if ($to instanceof Address) {
                        $this->to->Add($to);
                    } elseif (Variable::IsArray($to)) {
                        $this->to->AddRange($to);
                    } elseif (Variable::IsString($to)) {
                        $emails = explode(';', $to);
                        foreach ($emails as $v) {
                            $this->to->Add(new Address($v));
                        }
                    }
                }
                
                if (!Variable::IsEmpty($subject)) {
                    $this->subject = $subject;
                }
                
                $this->attachments = new AttachmentList();
            }
            
            public function __get($property)
            {
                $property = strtolower($property);
                if ($property != 'type') {
                    return $this->$property;
                }

                $return = null;
                if ($this->attachments->count < 1 && strlen($this->altbody) < 1) {
                    $return = 'plain';
                } else {
                    if ($this->attachments->count > 0) {
                        $return = 'attachments';
                    }
                    if (strlen($this->altbody) > 0 && $this->attachments->count < 1) {
                        $return = 'alt';
                    }
                    if (strlen($this->altbody) > 0 && $this->attachments->count > 0) {
                        $return = 'alt_attachments';
                    }
                }
                
                return $return;
            }
            
            public function __set($property, $value)
            {
                if (strtolower($property) == 'altbody') {
                    // Set whether the message is multipart/alternative
                    if (!Variable::IsEmpty($value)) {
                        $this->contenttype = 'multipart/alternative';
                    } else {
                        $this->contenttype = "text/plain";
                    }
                }
                
                $this->$property = $value;
            }
            
            public function IncludeEmbededImages($message, $basedir = '')
            {
                preg_match_all("/(src|background)=\"(.*)\"/Ui", $message, $images);

                $imagesList = isset($images[2]) ? $images[2] : [];
                foreach ($imagesList as $i => $url) {
                    
                    // do not change urls for absolute images (thanks to corvuscorax)
                    if (preg_match('#^[A-z]+://#', $url)) {
                        continue;
                    }

                    $filename = basename($url);
                    
                    $directory = dirname($url);
                    if ($directory == '.') {
                        $directory = '';
                    }
                        
                    $cid = 'cid:' . md5($filename);
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    $mimeType  = MimeType::Create($ext)->type;
                    
                    if (strlen($basedir) > 1 && substr($basedir, -1) != '/') {
                        $basedir .= '/';
                    }
                    
                    if (strlen($directory) > 1 && substr($directory, -1) != '/') {
                        $directory .= '/';
                    }
                    
                    try {
                        $ma = Attachment::CreateEmbeded($basedir.$directory.$filename, md5($filename), $filename, 'base64', $mimeType);
                        $message = preg_replace("/".$images[1][$i]."=\"".preg_quote($url, '/')."\"/Ui", $images[1][$i]."=\"".$cid."\"", $message);
                        $this->attachments->Add($ma);
                    } catch (Exception $e) {
                        $message = '';
                        $ma = null;
                    }
                }
                
                $this->contenttype = 'text/html';
                $this->body = $message;
                $textMsg = trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/s', '', $message)));
                if (!Variable::IsEmpty($textMsg) && Variable::IsEmpty($this->altbody)) {
                    $this->altbody = html_entity_decode($textMsg);
                }

                if (Variable::IsEmpty($this->altbody)) {
                    $this->altbody = 'To view this email message, open it in a program that understands HTML!' . "\n\n";
                }
            }
        }
        
    }
