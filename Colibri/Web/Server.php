<?php
    /**
     * Веб сервер
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Utils\Config
     * 
     * 
     */
    namespace Colibri\Web {

        use Colibri\App;
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

            /**
             * Список ошибок
             */
            const IncorrectCommandObject = 1;
            const UnknownMethodInObject = 2;

            /**
             * Список типов
             */
            const JSON = 'json';
            const XML = 'xml';
            const HTML = 'html';
            const CSS = 'css';
            const JS = 'js';

            /**
             * Конструктор
             *
             * @param integer $type
             */
            public function __construct() {

            }

            /**
             * Отображает результат
             *
             * @param mixed $result
             * @return void
             */
            protected function View($type, $result) {
                if ($result->result) {
                    if ($type == Server::JSON) {
                        App::$response->Close($result->code, json_encode($result->result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    } elseif ($type == Server::XML) {
                        App::$response->Close($result->code, XmlEncoder::Encode($result->result));
                    } elseif ($type == Server::HTML) {
                        App::$response->Close($result->code, HtmlEncoder::Encode($result->result));
                    }
                }
                else {
                    App::$response->Close($result->code, $result->message);
                }
            }

            /**
             * Отправляет ответ об ошибке
             *
             * @param string $message
             * @param integer $code
             * @return void
             */
            protected function _responseWithError($type, $message, $code = -1, $cmd = '', $data = null) {

                $this->View($type, (object)[
                    'code' => 404,
                    'message' => $message,
                    'result' => (object)[
                        'code' => $code, 
                        'command' => $cmd,
                        'data' => $data
                    ]
                ]);

            }

            protected function _getTransformerFullName($class) {
                $class = Strings::UrlToNamespace($class);
                if(strpos($class, 'Modules') === 0) {

                    $parts = explode('\\', $class);
                    $parts[count($parts) - 1] = 'Transformers\\'.$parts[count($parts) - 1];
                    $class = implode('\\', $parts);

                    return '\\App\\'.$class.'Transformer';
                }
                return '\\App\\Transformers\\'.$class.'Transformer';
            }

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
                
                return (object)[
                    'cmd' => $cmd,
                    'class' => $class,
                    'transformer' => $this->_getTransformerFullName($class), 
                    'method' => Strings::ToCamelCaseAttr($method, true), 
                    'type' => $type
                ];
                
            }

            /**
             * Запускает команду
             * 
             * Команда должна быть сформирована следующим образом
             * папки, после \App\Transformers превращаются в namespace
             * т.е. /buh/test-rpc/test-query.json 
             * будет превращено в \App\Transformers\Buh\TestRpcController
             * а метод будет TestQuery 
             * 
             * т.е. нам нужно получить lowercase url в котором все большие 
             * буквы заменяются на - и маленькая буква, т.е. test-rpc = TestRpc
             *
             * @return string Результат работы в виде строки JSON или XML
             */
            public function Run($cmd)
            {

                // /namespace[/namespace]/command[.type]
                $parsed = $this->_parseCommandLine($cmd);
                $transformer = $parsed->transformer;
                $method = $parsed->method;
                $type = $parsed->type;

                $get = App::$request->get;
                $post = App::$request->post;
                $payload = App::$request->GetPayloadCopy();
                
                $args = (object)['transformer' => $transformer, 'get' => $get, 'post' => $post, 'payload' => $payload];
                $this->DispatchEvent(EventsContainer::ServerGotRequest, $args);
                if (isset($args->cancel) && $args->cancel === true) {
                    $result = isset($args->result) ? $args->result : (object)[];
                    $this->View($type, $result);
                }

                if (!class_exists($transformer)) {
                    $message = 'Unknown transformer '.$transformer;
                    $this->DispatchEvent(EventsContainer::ServerRequestError, (object)[
                        'transformer' => $transformer,
                        'get' => $get,
                        'post' => $post,
                        'payload' => $payload,
                        'message' => $message
                    ]);

                    $this->_responseWithError($type, $message, Server::IncorrectCommandObject, $cmd, [
                        'message' => $message,
                        'code' => Server::IncorrectCommandObject,
                        'get' => $get,
                        'post' => $post,
                        'payload' => $payload
                    ]);
                    return false;
                }

                // ищем метод $method, если есть то это просто контроллер без view
                // если не найден ищем $methodController, если есть тогда предполагаем наличие $methodView
                $realMethodName = $method;
                $realViewName = null;
                if (!method_exists($transformer, $realMethodName)) {
                    $realMethodName = $method.'Controller';
                    $realViewName = $method.'View';
                }                

                if (!method_exists($transformer, $realMethodName) || ($realViewName && !method_exists($transformer, $realViewName))) {
                    $message = 'Can not find method Controller and/or View method in '.$transformer;
                    $this->DispatchEvent(EventsContainer::ServerRequestError, (object)[
                        'transformer' => $transformer,
                        'method' => $method,
                        'get' => $get,
                        'post' => $post,
                        'payload' => $payload,
                        'message' => $message
                    ]);

                    $this->_responseWithError(
                        $type, 
                        $message,
                        Server::UnknownMethodInObject,
                        $cmd,
                        [
                            'message' => $message,
                            'code' => Server::UnknownMethodInObject,
                            'get' => $get,
                            'post' => $post,
                            'payload' => $payload
                        ]
                    );
                    return false;
                }

                $transformerObject = new $transformer($this);
                $result = (object)$transformerObject->$realMethodName($get, $post, $payload);

                $this->DispatchEvent(EventsContainer::ServerRequestProcessed, (object)[
                    'controller' => $transformerObject,
                    'get' => $get,
                    'post' => $post,
                    'payload' => $payload,
                    'result' => $result
                ]);

                // после выполнения контроллера, ищем соответствующий метод View и 
                // если нет метода, то пропускаем
                if($realViewName) {
                    $transformerObject->$realViewName($result);
                }
                else {
                    $this->View($type, $result);
                }
                
            }

        }

    }