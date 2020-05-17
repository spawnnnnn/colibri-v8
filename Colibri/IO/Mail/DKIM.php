<?php
    /**
     * Mail
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Mail
     */
    namespace Colibri\IO\Mail {

        /**
         * Информация о DKIM
         */
        class DKIM
        {
            /**
             * Секетор
             *
             * @var string
             */
            private $_selector   = 'phpmailer';

            /**
             * Идентификатор
             *
             * @var string
             */
            private $_identity   = '';

            /**
             * Домен
             *
             * @var string
             */
            private $_domain     = '';

            /**
             * Приватный ключс
             *
             * @var string
             */
            private $_private    = '';
            
            /**
             * Конструктор
             *
             * @param string $selector селектор
             * @param string $identity идентификатор
             * @param string $domain домен
             * @param string $private приватный ключ
             */
            public function __construct($selector, $identity, $domain, $private)
            {
                $this->_selector = $selector;
                $this->_identity = $identity;
                $this->_domain = $domain;
                $this->_private = $private;
            }
            
            /**
             * Геттер
             *
             * @param string $property свойство
             * @return string
             */
            public function __get($property)
            {
                $name = "_".strtolower($property);
                return $this->$name;
            }
            
            /**
             * Сеттер
             *
             * @param string $property свойство
             * @param string $value значение
             */
            public function __set($property, $value)
            {
                $name = "_".strtolower($property);
                $this->$name = $value;
            }
            
            /**
             * Кодирует строку
             * @param string $txt
             */
            private function QP($txt)
            {
                $line="";
                for ($i=0; $i<strlen($txt); $i++) {
                    $ord = ord($txt[$i]);
                    if (((0x21 <= $ord) && ($ord <= 0x3A)) || $ord == 0x3C || ((0x3E <= $ord) && ($ord <= 0x7E))) {
                        $line.=$txt[$i];
                    } else {
                        $line.="=".sprintf("%02X", $ord);
                    }
                }
                return $line;
            }

            /**
             * Создает DKIM ключ
             *
             * @param string $s заголовок
             */
            private function Sign($s)
            {
                $privKeyStr = file_get_contents($this->_private);
                if ($this->_passphrase!='') {
                    $privKey = openssl_pkey_get_private($privKeyStr, $this->_passphrase);
                } else {
                    $privKey = $privKeyStr;
                }
                if (openssl_sign($s, $signature, $privKey)) {
                    return base64_encode($signature);
                }
                return false;
            }

            /**
             * Создает основной заголовок DKIM
             *
             * @param string $s заголовок
             */
            private function HeaderC($s)
            {
                $s = preg_replace("/\r\n\s+/", " ", $s);
                $lines = explode(Helper::CRLF, $s);
                foreach ($lines as $key => $line) {
                    list($heading, $value)=explode(":", $line, 2);
                    $heading = strtolower($heading);
                    $value = preg_replace("/\s+/", " ", $value) ; // Compress useless spaces
                    $lines[$key] = $heading.":".trim($value) ; // Don't forget to remove WSP around the value
                }
                return implode("\r\n", $lines);
            }

            /**
             * Создает основной контент DKIM
             *
             * @param string $body содержание письма
             */
            private function BodyC($body)
            {
                if ($body == '') {
                    return Helper::CRLF;
                }
                $body = str_replace(Helper::CRLF, Helper::LF, $body);
                $body = str_replace(Helper::LF, Helper::CRLF, $body);
                while (substr($body, strlen($body)-4, 4) == Helper::CRLF.Helper::CRLF) {
                    $body = substr($body, 0, strlen($body)-2);
                }
                return $body;
            }
            
            /**
             * Создает заголовок и тело письма с DKIM
             *
             * @param string[] $headers строки заголовка
             * @param string $subject Тема письма
             * @param string $body Тело письма
             */
            public function Add($headers, $subject, $body)
            {
                $DKIMsignatureType    = 'rsa-sha1'; // Signature & hash algorithms
                $DKIMcanonicalization = 'relaxed/simple'; // Canonicalization of header/body
                $DKIMquery            = 'dns/txt'; // Query method
                $DKIMtime             = time() ; // Signature Timestamp = seconds since 00:00:00 - Jan 1, 1970 (UTC time zone)
                $subject_header       = "Subject: $subject";
                $headers              = explode(Helper::CRLF, $headers);
                
                foreach ($headers as $header) {
                    if (strpos($header, 'From:') === 0) {
                        $from_header = $header;
                    } elseif (strpos($header, 'To:') === 0) {
                        $to_header = $header;
                    }
                }
                
                $from     = str_replace('|', '=7C', $this->QP($from_header));
                $to       = str_replace('|', '=7C', $this->QP($to_header));
                $subject  = str_replace('|', '=7C', $this->QP($subject_header)) ; // Copied header fields (dkim-quoted-printable
                
                $body     = $this->BodyC($body);
                
                $DKIMlen  = strlen($body) ; // Length of body
                $DKIMb64  = base64_encode(pack("H*", sha1($body))) ; // Base64 of packed binary SHA-1 hash of body
                $ident    = ($this->_identity == '')? '' : " i=" . $this->_identity . ";";
                $dkimhdrs = "DKIM-Signature: v=1; a=" . $DKIMsignatureType . "; q=" . $DKIMquery . "; l=" . $DKIMlen . "; s=" . $this->_selector . ";\r\n".
                            "\tt=" . $DKIMtime . "; c=" . $DKIMcanonicalization . ";\r\n".
                            "\th=From:To:Subject;\r\n".
                            "\td=" . $this->_domain . ";" . $ident . "\r\n".
                            "\tz=$from\r\n".
                            "\t|$to\r\n".
                            "\t|$subject;\r\n".
                            "\tbh=" . $DKIMb64 . ";\r\n".
                            "\tb=";
                $toSign   = $this->HeaderC($from_header . "\r\n" . $to_header . "\r\n" . $subject_header . "\r\n" . $dkimhdrs);
                $signed   = $this->Sign($toSign);
                return "X-PHPMAILER-DKIM: phpmailer.worxware.com\r\n".$dkimhdrs.$signed."\r\n";
            }
        }
    }
