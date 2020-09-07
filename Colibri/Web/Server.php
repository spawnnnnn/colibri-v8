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

        use Colibri\App;
        use Colibri\AppException;
        use Colibri\Helpers\XmlEncoder;
        use Colibri\Helpers\HtmlEncoder;
        use Colibri\Events\TEventDispatcher;
        use Colibri\Events\EventsContainer;
        use Colibri\Helpers\Strings;
        
        /**
         * Веб сервер
         */
        class Server {

            
            use TEventDispatcher;

            /** Команда не найдена */
            const IncorrectCommandObject = 1;

            /** Метод не найден */
            const UnknownMethodInObject = 2;

            /** Вернуть в виде JSON */
            const JSON = 'json';
            /** Вернуть в виде XML */
            const XML = 'xml';
            /** Вернуть в виде HTML */
            const HTML = 'html';
            /** Вернуть в виде CSS */
            const CSS = 'css';
            /** Вернуть в виде JS */
            const JS = 'js';

            /**
             * Данные полученные при последней обработке запроса
             *
             * @var stdClass
             */
            private $_lastParsedData = null;
            

            /**
             * Конструктор
             */
            public function __construct() {
                // Do nothing
            }

            /**
             * Отображает результат
             *
             * @param string $type тип ответа
             * @param mixed $result результ
             * @return void
             */
            public function CloseResponse($type, $result) {
                if(isset($result->result)) {
                    if ($result->result) {
                        if ($type == Server::JSON) {
                            App::Response()->Close($result->code, json_encode($result->result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        } elseif ($type == Server::XML) {
                            App::Response()->Close($result->code, XmlEncoder::Encode($result->result));
                        } elseif ($type == Server::HTML) {
                            App::Response()->Close($result->code, HtmlEncoder::Encode($result->result));
                        }
                    }
                    else {
                        App::Response()->Close($result->code, $result->message);
                    }
                }
            }

            /**
             * Возвращает полное название класса трансформера
             *
             * @param string $class текст запроса
             * @return string
             */
            protected function _getHandlerFullName($class) {
                $class = Strings::UrlToNamespace($class);
                if(strpos($class, 'Modules') === 0) {

                    $parts = explode('\\', $class);
                    $parts[count($parts) - 1] = 'Handlers\\'.$parts[count($parts) - 1];
                    $class = implode('\\', $parts);

                    return '\\App\\'.$class.'Handler';
                }
                return '\\App\\Handlers\\'.$class.'Handler';
            }

            /**
             * Собирает данные о трансформере по команде
             *
             * @param string $cmd
             * @return stdClass
             */
            protected function _parseCommandLine($cmd) {
                $res = preg_match('/\/([^\/]+)\.(.+)/', $cmd, $matches);

                $method = 'index';
                $type = Server::HTML;
                if($res > 0) {
                    $method = $matches[1];
                    $type = $matches[2];
                }

                $class = str_replace($method.'.'.$type, '', $cmd);
                $class = trim($class, '/');
                if(!$class) {
                    $class = 'index';
                }

                $this->_lastParsedData = (object)[
                    'cmd' => $cmd,
                    'class' => $class,
                    'handler' => $this->_getHandlerFullName($class), 
                    'method' => Strings::ToCamelCaseAttr($method, true), 
                    'type' => $type
                ];
                
                return $this->_lastParsedData;
                
            }

            /**
             * Запускает команду
             * 
             * Команда должна быть сформирована следующим образом
             * папки, после \App\Handlers превращаются в namespace
             * т.е. /buh/test-rpc/test-query.json 
             * будет превращено в \App\Handlers\Buh\TestRpcController
             * а метод будет TestQuery 
             * 
             * т.е. нам нужно получить lowercase url в котором все большие 
             * буквы заменяются на - и маленькая буква, т.е. test-rpc = TestRpc
             * 
             * @param string $cmd команда
             *
             * @return string Результат работы в виде строки JSON или XML
             */
            public function Run($cmd)
            {

                // /namespace[/namespace]/command[.type]
                $parsed = $this->_parseCommandLine($cmd);
                $handler = $parsed->handler;
                $method = $parsed->method;
                $type = $parsed->type;

                $get = App::Request()->get;
                $post = App::Request()->post;
                $payload = App::Request()->GetPayloadCopy();
                
                $args = (object)['handler' => $handler, 'get' => $get, 'post' => $post, 'payload' => $payload];
                $this->DispatchEvent(EventsContainer::ServerGotRequest, $args);
                if (isset($args->cancel) && $args->cancel === true) {
                    $result = isset($args->result) ? $args->result : (object)[];
                    $this->CloseResponse($type, $result);
                }

                if (!class_exists($handler)) {
                    $message = 'Unknown handler '.$handler;
                    $this->DispatchEvent(EventsContainer::ServerRequestError, (object)[
                        'handler' => $handler,
                        'get' => $get,
                        'post' => $post,
                        'payload' => $payload,
                        'message' => $message
                    ]);
                    
                    throw new AppException($message, 404, [
                        'cmd' => $cmd,
                        'type' => $type,
                        'message' => $message,
                        'code' => Server::IncorrectCommandObject,
                        'get' => $get,
                        'post' => $post,
                        'payload' => $payload
                    ]);

                }

                // ищем метод $method, если есть то это просто контроллер без view
                // если не найден ищем $methodController, если есть тогда предполагаем наличие $methodView
                $realMethodName = $method;
                $realViewName = null;
                if (!method_exists($handler, $realMethodName)) {
                    $realMethodName = $method.'Controller';
                    $realViewName = $method.'View';
                }                
 
                if (!method_exists($handler, $realMethodName) || ($realViewName && !method_exists($handler, $realViewName))) {
                    $message = 'Can not find method Controller and/or View method in '.$handler;
                    $this->DispatchEvent(EventsContainer::ServerRequestError, (object)[
                        'handler' => $handler,
                        'method' => $method,
                        'get' => $get,
                        'post' => $post,
                        'payload' => $payload,
                        'message' => $message
                    ]);

                    throw new AppException($message, 404, [
                        'cmd' => $cmd,
                        'type' => $type,
                        'message' => $message,
                        'code' => Server::UnknownMethodInObject,
                        'get' => $get,
                        'post' => $post,
                        'payload' => $payload
                    ]);

                }

                $handlerObject = new $handler($this);
                $result = (object)$handlerObject->$realMethodName($get, $post, $payload);

                $this->DispatchEvent(EventsContainer::ServerRequestProcessed, (object)[
                    'controller' => $handlerObject,
                    'get' => $get,
                    'post' => $post,
                    'payload' => $payload,
                    'result' => $result
                ]);

                // после выполнения контроллера, ищем соответствующий метод View и 
                // если нет метода, то пропускаем
                if($realViewName) {
                    $handlerObject->$realViewName($result);
                }
                else {
                    $this->CloseResponse($type, $result);
                }
                
            }

            /**
             * Геттер
             *
             * @param string $property
             * @return mixed
             */
            public function __get($property) {
                $return = null;
                if(strtolower($property) == 'cmd' && $this->_lastParsedData !== null) {
                    $return = $this->_lastParsedData->cmd;
                }
                else if(strtolower($property) == 'class' && $this->_lastParsedData !== null) {
                    $return = $this->_lastParsedData->class;
                }
                else if(strtolower($property) == 'handler' && $this->_lastParsedData !== null) {
                    $return = $this->_lastParsedData->handler;
                }
                else if(strtolower($property) == 'method' && $this->_lastParsedData !== null) {
                    $return = $this->_lastParsedData->method;
                }
                else if(strtolower($property) == 'type' && $this->_lastParsedData !== null) {
                    $return = $this->_lastParsedData->type;
                }
                return $return;
            }

        }

    }