<?php
    /**
     * Класс запроса
     * 
     * @author Ваган Григорян <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Web
     * @version 1.0.0
     * 
     */
    namespace Colibri\Web {

        use Colibri\Events\TEventDispatcher;
        use Colibri\Events\EventsContainer;
        use Colibri\Helpers\XmlEncoder;

        /**
         * Класс запроса
         * 
         * @property-read RequestCollection $get
         * @property-read RequestCollection $post
         * @property-read RequestFileCollection $files
         * @property-read RequestCollection $session
         * @property-read RequestCollection $server
         * @property-read RequestCollection $cookie
         * @property-read RequestCollection $headers
         * @property-read string $remoteip
         * @property-read string $uri
         * @property-read string $host
         * @property-read string $address
         * @property-read string $type
         * 
         */
        class Request {

            // подключаем функционал событийной модели
            use TEventDispatcher;

            /**
            * Singlton
            *
            * @var Request
            *
            */
            static $instance;

            /** Тип запроса JSON */
            const PAYLOAD_TYPE_JSON = 'json';
            /** Тип запроса XML */
            const PAYLOAD_TYPE_XML = 'xml';

            /**
             * Payload считанный из php://input-а
             *
             * @var stdClass
             */
            private $_requestPayload;

            /**
             * Конструктор
             */
            private function __construct() { 

                $payload = file_get_contents('php://input');
                if(!$payload) {
                    $this->_jsonPayload = null;
                }
                else {
                    $this->_requestPayload =  $payload;
                }

                $this->DispatchEvent(EventsContainer::RequestReady);

            }

            /**
             * Статический контруктор
             *
             * @return Request
             */
            public static function Create() {
                if(!Request::$instance) {
                    Request::$instance = new Request();
                }
                return Request::$instance;
            }

            /**
             * Возвращает URI с добавлением или удалением параметров
             *
             * @param array $add
             * @param array $remove
             * @return string
             */
            public function Uri($add = array(), $remove = array()) {
                $get = $this->get->ToArray();
                foreach($remove as $v) {
                    unset($get[$v]);
                }
                foreach($add as $k => $v) {
                    $get[$k] = $v;
                }
                $url = '';
                foreach($get as $k => $v) {
                    $url .= '&'.$k.'='.$v;
                }
                return '?'.substr($url, 1);
            }

            /**
             * Магический метод
             *
             * @param string $prop
             * @return mixed
             */
            public function __get($prop) {
                $prop = strtolower($prop);
                $return = null;
                switch($prop) {
                    case 'get': {
                        $return = new RequestCollection($_GET);
                        break;
                    }
                    case 'post': {
                        $return = new RequestCollection($_POST);
                        break;
                    }
                    case 'files':{
                        $return = new RequestFileCollection($_FILES);
                        break;
                    }
                    case 'session':{
                        $return = new RequestCollection($_SESSION);
                        break;
                    }
                    case 'server':{
                        $return = new RequestCollection($_SERVER);
                        break;
                    }
                    case 'cookie':{
                        $return = new RequestCollection($_COOKIE);
                        break;
                    }
                    case 'remoteip': {
                        if($this->server->HTTP_X_FORWARDED_FOR) {
                            $return = $this->server->HTTP_X_FORWARDED_FOR;
                        }
                        else if($this->server->REMOTE_ADDR) {
                            $return = $this->server->REMOTE_ADDR;
                        }
                        else if($this->server->X_REAL_IP) {
                            $return = $this->server->X_REAL_IP;
                        }
                        else if($this->server->HTTP_FORWARDED) {
                            $return = $this->server->HTTP_FORWARDED;
                        }
                        else {
                            $return = '';
                        }
                        break;
                    }
                    case 'uri': {
                        $return = $this->server->request_uri ? $this->server->request_uri : '';
                        break;
                    }
                    case 'host': {
                        $return = $this->server->http_host ? $this->server->http_host : '';
                        break;
                    }
                    case 'address': {
                        $proto = $this->server->https ? 'https://' : 'http://';
                        $return = $this->server->http_host ? $proto.$this->server->http_host : '';
                        break;
                    }
                    case 'headers': {
                        $headers = [];
                        foreach($this->server as $key => $value) {
                            if(strpos($key, 'http_') === 0) {
                                $headers[substr($key, 5)] = $value;
                            }
                        }
                        $return = new RequestCollection($headers);
                        break;
                    }
                    case 'type': {
                        $return = $this->server->request_method ? $this->server->request_method : 'get';
                        break;
                    }
                    default: {
                        return null;
                    }
                }
                return $return;
            }

            /**
             * Возвращает копию RequestPayload в виде обьекта или SimpleXMLElement
             * 
             * @param string $type тип результата
             * 
             */
            public function GetPayloadCopy($type = Request::PAYLOAD_TYPE_JSON) {

                if (!$this->_requestPayload) {
                    return null;
                }
                
                $return = null;
                if($type == Request::PAYLOAD_TYPE_JSON) {
                    $return = json_decode($this->_requestPayload);
                }
                else if($type == Request::PAYLOAD_TYPE_XML) {
                    $return = XmlEncoder::Decode($this->_requestPayload);
                }

                return $return;
                
            }

        }
        
    }