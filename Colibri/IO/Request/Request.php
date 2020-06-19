<?php
    /**
     * Request
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Request
     */
    namespace Colibri\IO\Request {

        use Colibri\App;
        use Colibri\IO\FileSystem\File;
        use Colibri\Helpers\Strings;
        use Colibri\Helpers\Variable;
    use Colibri\Helpers\XmlEncoder;

/**
         * Класс запроса
         */
        class Request {

            /** Разделитель */
            const Boundary = '---------------------------';
            /** Окончание */
            const BoundaryEnd = '--';

            /**
             * Логины и пароли
             *
             * @var RequestCredentials
             */
            public $credentials;

            /**
             * Адрес
             *
             * @var string
             */
            public $target;
            /**
             * Метод
             *
             * @var string
             */
            public $method = Type::Get;
            /**
             * Данные
             *
             * @var Data | string | null
             */
            public $postData = null;
            /**
             * Шифрование
             *
             * @var string
             */
            public $encryption = Encryption::UrlEncoded;
            /**
             * Разделитель
             *
             * @var string
             */
            public $boundary = null;
            /**
             * Таймаут запроса
             *
             * @var integer
             */
            public $timeout = 60;
            /**
             * Таймаут в миллисекундах
             *
             * @var int | false
             */
            public $timeout_ms = false;
            /**
             * Индикатор ассинхронности
             *
             * @var boolean
             */
            public $async = false;
            /**
             * Куки
             *
             * @var array
             */
            public $cookies = array();
            /**
             * Файл куки
             *
             * @var string
             */
            public $cookieFile = '';
            /**
             * Реферер
             *
             * @var string
             */
            public $referer = '';
            /**
             * Заголовки
             *
             * @var array | false
             */
            public $headers = false;
            /**
             * UserAgent
             *
             * @var string
             */
            public $useragent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.122 Safari/534.30';
            /**
             * Проверять сертификат
             *
             * @var boolean
             */
            public $sslVerify = true;

            /**
             * Checks if the curl module loaded
             *
             */
            private static function __checkWebRequest() {
                return function_exists('curl_init');
            }

            /**
             * Конструктор
             *
             * @param string $target
             * @param string $method
             * @param string $encryption
             * @param Data $postData
             * @param string $boundary
             */
            public function __construct($target,
                                        $method = Type::Get,
                                        $encryption = Encryption::UrlEncoded,
                                        $postData = null,
                                        $boundary = '') {

                if(!self::__checkWebRequest()) {
                    throw new Exception('Can not load module curl.', 500);
                }

                // create boundary
                $this->boundary = !Variable::IsEmpty($boundary) ? $boundary : Strings::Randomize(8);

                $this->target = $target;
                $this->method = $method;
                $this->postData = $postData;

                $this->credentials = null;

                $this->encryption = $encryption;
            }

            /**
             * Создает данные запроса типа Multipart/Formdata
             *
             * @param string $boundary разделитель
             * @param mixed $files данные
             * @return string
             */
            private function _createMultipartRequestBody($boundary, $files){
                $data = '';
                $eol = "\r\n";

        
                $delimiter = Request::Boundary . $boundary;
                foreach ($files as $content) {
                    if ($content instanceof DataFile) {
                        $data .= "--" . $delimiter . $eol
                            . 'Content-Disposition: form-data; name="' . $content->name . '"; filename="' . $content->file . '"' . $eol
                            . 'Content-Transfer-Encoding: binary'.$eol;
        
                        $data .= $eol;
                        $data .= $content->value . $eol;
                    }
                    else if ($content instanceof DataItem) {
                            $data .= "--" . $delimiter . $eol
                                . 'Content-Disposition: form-data; name="' . $content->name . "\"".$eol.$eol
                                . $content->value . $eol;
                    }
                }
        
                return $data . "--" . $delimiter . Request::BoundaryEnd.$eol;
            }

            /**
             * Собирает пост
             *
             * @return string
             */
            private function _joinPostData() {
                $return = null;
                $data = array();

                if($this->encryption == Encryption::Multipart) {
                    return $this->_createMultipartRequestBody($this->boundary, $this->postData);
                }
                else if($this->encryption == Encryption::XmlEncoded) {
                    $return = Variable::IsString($this->postData) ? $this->postData : XmlEncoder::Encode($this->postData, null, false);
                }
                else if($this->encryption == Encryption::JsonEncoded) {
                    $return = Variable::IsString($this->postData) ? $this->postData : json_encode($this->postData);
                }
                else {

                    foreach($this->postData as $value) {
                        $data[] = $value->name.'='.rawurlencode($value->value);
                    }
                    $return = implode("&", $data);

                }
                
                return $return;

            }

            /**
             * Выполняет запрос
             *
             * @param mixed $postData
             * @return Result
             */
            public function Execute($postData = null) {

                if(!Variable::IsNull($postData)) {
                    $this->postData = $postData;
                }

                $handle = curl_init();

                curl_setopt($handle, CURLOPT_URL, $this->target);
                if(!$this->async) {
                    curl_setopt($handle, CURLOPT_TIMEOUT, $this->timeout);
                }
                else {
                    if($this->timeout_ms) {
                        curl_setopt($handle, CURLOPT_TIMEOUT_MS, $this->timeout_ms ? $this->timeout_ms : 100);
                    }
                    else {
                        curl_setopt($handle, CURLOPT_TIMEOUT_MS, $this->timeout ? $this->timeout * 1000 : 100);
                    }
                    curl_setopt($handle, CURLOPT_NOSIGNAL, 1);
                }

                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);

                if(!empty($this->referer)) {
                    curl_setopt($handle, CURLOPT_REFERER, $this->referer);
                }
                else {
                    curl_setopt($handle, CURLOPT_REFERER, $_SERVER['SERVER_NAME']);
                }
                if(!empty($this->cookieFile)) {
                    if ( ! File::Exists($this->cookieFile)) {
                        File::Create($this->cookieFile);
                    }
                    curl_setopt($handle, CURLOPT_COOKIEJAR, $this->cookieFile);
                    curl_setopt($handle, CURLOPT_COOKIEFILE, $this->cookieFile);
                }

                if(!Variable::IsNull($this->credentials)){
                    curl_setopt($handle, CURLOPT_USERPWD, $this->credentials->login.':'.$this->credentials->secret);
                    if($this->credentials->ssl) {
                        curl_setopt($handle, CURLOPT_USE_SSL, true);
                    }
                }

                $_headers = array(
                    "Connection: Keep-Alive",
                    'HTTP_X_FORWARDED_FOR: '.App::Request()->remoteip,
                    'Expect:'
                );

                if ($this->cookies) {
                    $_headers[] = "Cookie: ".is_array($this->cookies) ? http_build_query($this->cookies, '', '; ') : $this->cookies;
                }

                if($this->encryption == Encryption::Multipart) {
                    curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'POST');
                    $_headers[] = "Content-Type: multipart/form-data; boundary=".Request::Boundary.$this->boundary;
                }
                else if($this->encryption == Encryption::JsonEncoded) {
                    $_headers[] = "Content-Type: application/json";
                }
                
                if($this->method == Type::Post) {
                    curl_setopt($handle, CURLOPT_POST, true);
                    if(!Variable::IsNull($this->postData)) {
                        $data = $this->_joinPostData($this->postData);
                        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
                    }
                }
                else if($this->method == Type::Get) {
                    curl_setopt($handle, CURLOPT_HTTPGET, true);
                }
                else {
                    curl_setopt($handle, CURLOPT_CUSTOMREQUEST, Strings::ToUpper($this->method));
                    if(!Variable::IsNull($this->postData)) {
                        $data = $this->_joinPostData($this->postData);
                        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
                    }
                }

                if(is_array($this->headers)){
                    $_headers = array_merge($this->headers, $_headers);
                }
                
                curl_setopt($handle, CURLOPT_HTTPHEADER, $_headers);

                if ($this->useragent) {
                    curl_setopt($handle, CURLOPT_USERAGENT, $this->useragent);
                }

                if ( ! $this->sslVerify) {
                    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, $this->sslVerify);
                    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $this->sslVerify);
                }

                $result = new Result();

                $result->data = curl_exec($handle);
                $result->status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
                $result->headers = curl_getinfo($handle);

                curl_close($handle);

                return $result;
            }





        }

    }