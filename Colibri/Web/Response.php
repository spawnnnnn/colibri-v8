<?php

    /**
     * Web
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Web
     * 
     * 
     */
    namespace Colibri\Web {

        use Colibri\Helpers\MimeType;
        use Colibri\Events\TEventDispatcher;
        use Colibri\Events\EventsContainer;
    
        /**
         * Респонс 
         */
        class Response 
        {

            // подключаем функционал событийной модели
            use TEventDispatcher;
    
            /**
            * Синглтон
            *
            * @var Response
            */
            static $instance;
    
            /**
             * Коды ответов
             */
            static $codes = array(
                100 => 'Continue',
                101 => 'Switching Protocols',
                102 => 'Processing', // WebDAV; RFC 2518
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information', // since HTTP/1.1
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                207 => 'Multi-Status', // WebDAV; RFC 4918
                208 => 'Already Reported', // WebDAV; RFC 5842
                226 => 'IM Used', // RFC 3229
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other', // since HTTP/1.1
                304 => 'Not Modified',
                305 => 'Use Proxy', // since HTTP/1.1
                306 => 'Switch Proxy',
                307 => 'Temporary Redirect', // since HTTP/1.1
                308 => 'Permanent Redirect', // approved as experimental RFC
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Requested Range Not Satisfiable',
                417 => 'Expectation Failed',
                418 => 'I\'m a teapot', // RFC 2324
                419 => 'Authentication Timeout', // not in RFC 2616
                420 => 'Enhance Your Calm', // Twitter
                420 => 'Method Failure', // Spring Framework
                422 => 'Unprocessable Entity', // WebDAV; RFC 4918
                423 => 'Locked', // WebDAV; RFC 4918
                424 => 'Failed Dependency', // WebDAV; RFC 4918
                424 => 'Method Failure', // WebDAV)
                425 => 'Unordered Collection', // Internet draft
                426 => 'Upgrade Required', // RFC 2817
                428 => 'Precondition Required', // RFC 6585
                429 => 'Too Many Requests', // RFC 6585
                431 => 'Request Header Fields Too Large', // RFC 6585
                444 => 'No Response', // Nginx
                449 => 'Retry With', // Microsoft
                450 => 'Blocked by Windows Parental Controls', // Microsoft
                451 => 'Redirect', // Microsoft
                451 => 'Unavailable For Legal Reasons', // Internet draft
                494 => 'Request Header Too Large', // Nginx
                495 => 'Cert Error', // Nginx
                496 => 'No Cert', // Nginx
                497 => 'HTTP to HTTPS', // Nginx
                499 => 'Client Closed Request', // Nginx
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported',
                506 => 'Variant Also Negotiates', // RFC 2295
                507 => 'Insufficient Storage', // WebDAV; RFC 4918
                508 => 'Loop Detected', // WebDAV; RFC 5842
                509 => 'Bandwidth Limit Exceeded', // Apache bw/limited extension
                510 => 'Not Extended', // RFC 2774
                511 => 'Network Authentication Required', // RFC 6585
                598 => 'Network read timeout error', // Unknown
                599 => 'Network connect timeout error', // Unknown
            );
    
            /**
             * Конструктор
             */ 
            private function __construct() {
                $this->DispatchEvent(EventsContainer::ResponseReady);
            }
    
            /**
             * Статический конструктор
             *
             * @return Response
             */
            public static function Create() {
                if(!Response::$instance) {
                    Response::$instance = new Response();
                }
                return Response::$instance;
            }
    
            /**
             * Добавить хедер
             *
             * @param string $name название 
             * @param string $value значение
             * @return void
             */
            private function _addHeader($name, $value) {
                header($name.': '.$value);
            }
    
            /**
             * Добавить NoCache
             *
             * @return Response
             */
            public function NoCache() {
                $this->_addHeader('Pragma', 'no-cache');
                $this->_addHeader('X-Accel-Expires', '0');
                return $this;
            }
    
            /**
             * Добавить content-type
             *
             * @param string $type
             * @param string $encoding
             * @return Response
             */
            public function ContentType($type, $encoding = false) {
                $this->_addHeader('Content-type', $type.($encoding ? "; charset=".$encoding : ""));
                return $this;
            }
    
            /**
             * Добавить expires
             *
             * @param int $seconds
             * @return Response
             */
            public function ExpiresAfter($seconds) {
                $this->_addHeader('Expires', gmstrftime("%a, %d %b %Y %H:%M:%S GMT", time() - $seconds));
                return $this;
            }
    
            /**
             * Добавить expires
             *
             * @param int $date дата
             * @return Response
             */
            public function ExpiresAt($date) {
                $this->_addHeader('Expires', gmstrftime("%a, %d %b %Y %H:%M:%S GMT", $date));
                return $this;
            }
    
            /**
             * Добавить cache-control и все остальные приблуды
             *
             * @param int $seconds количество секунд
             * @return Response
             */
            public function Cache($seconds) {
                $this->_addHeader('Pragma', 'no-cache');
                $this->_addHeader('X-Accel-Expires', $seconds);
                return $this;
            }
    
            /**
             * Что то полезное
             *
             * @return Response
             */
            public function P3P() {
                $this->_addHeader('P3P', 'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
                return $this;
            }
    
            /**
             * Переадресация
             *
             * @param string $url url куда нужно переадресовать
             * @return Response
             */
            public function Redirect($url) {
                header('Location: '.$url);
                return $this;
            }
            
            /**
             * Добавить хедер Content-Description: File Transfer
             *
             * @return Response
             */
            public function FileTransfer() {
                $this->_addHeader('Content-Description', 'File Transfer');
                return $this;
            }
    
            /**
             * Добавить content-disposition
             *
             * @param string $type тип запрашеваемого файла
             * @param string $name название файла
             * @return Response
             */
            public function ContentDisposition($type, $name) {
                $this->_addHeader('Content-Disposition', $type.'; filename='.$name);
                return $this;
            }
    
            /**
             * Добавтиь content-transfer-encoding
             *
             * @param string $type тип запрашеваемых данных
             * @return Response
             */
            public function ContentTransferEncoding($type = 'binary') {
                $this->_addHeader('Content-Transfer-Encoding', $type);
                return $this;
            }
    
            /**
             * Добавить pragma
             *
             * @param string $type тип Pragma
             * @return Response
             */
            public function Pragma($type = 'binary') {
                $this->_addHeader('Pragma', $type);
                return $this;
            }
    
            /**
             * Добавтить content-length
             *
             * @param int $length длина
             * @return Response
             */
            public function ContentLength($length) {
                $this->_addHeader('Content-Length', $length);
                return $this;
            }
    
            /**
             * Добавить cache-control
             *
             * @param string $type тип документа (MIME)
             * @return Response
             */
            public function CacheControl($type) {
                $this->_addHeader('Cache-Control', $type);
                return $this;
            }

            /**
             * Вернуть ошибку и остановится 
             *
             * @param string $content контент, который нужно отправить в результат
             * @return void
             */
            public function Error404($content = '') {
                $this->Close(404, $content);
            }
    
            /**
             * Выдать ответ с результатом
             *
             * @param int $status стаус ответа
             * @param string $content контент
             * @return void
             */
            public function Close($status, $content = '') {
                header('HTTP/1.1 '.$status.' '.Response::$codes[$status]);
                echo $content;
                exit;
            }
    
            /**
             * Переадресация на загрузку файла
             *
             * @param string $filename название файла
             * @param string $filecontent контент файла
             * @return string
             */
            public function DownloadFile($filename, $filecontent) {
                $mime = new MimeType($filename);
                $this->FileTransfer();
                $this->ContentDisposition('attachment', $filename);
                $this->ContentType($mime->data);
                $this->ContentTransferEncoding('binary');
                $this->ExpiresAt(0);
                $this->CacheControl('must-revalidate');
                $this->Pragma('public');
                $this->ContentLength(strlen($filecontent));
                $this->Close(200, $filecontent);
            }

            /**
             * Аналог функции echo
             *
             * @return void
             */
            public function Write() {
                $args = func_get_args();
                foreach($args as $arg) {
                    echo $arg;
                }
                flush();
            }
    
        }
        
    }