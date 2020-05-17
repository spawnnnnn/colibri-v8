<?php
    /**
     * Colibri
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri
     * 
     */
    namespace Colibri {

        use Colibri\Web\Request;
        use Colibri\Web\Response;
        use Colibri\Configuration\Config;
        use Colibri\Events\EventDispatcher;
        use Colibri\Modules\ModuleManager;
        use Colibri\Security\SecurityManager;
        use Colibri\Events\TEventDispatcher;
        use Colibri\Events\EventsContainer;
        use Colibri\Data\DataAccessPoints;
        use Colibri\Utils\Debug;
        use Colibri\Utils\Logs\Logger;
        use Colibri\Web\Server;

        /**
         * Класс приложения
         */
        final class App
        {
            // подключаем функционал событийной модели
            use TEventDispatcher;

            /** Режим приложения в разработке */
            const ModeDevelopment   = 'development';
            /** Режим приложения в тестировании */
            const ModeTest          = 'test';
            /** Режим приложения в релизе */
            const ModeRelease       = 'release';

            /** Веб сервер */
            const ApplicationWebSerwer = 'webserver';
            /** RPC сервер */
            const ApplicationRpcSerwer = 'rpcserver';

            /**
             * Синглтон
             *
             * @var App
             */
            public static $instance;

            /**
             * Обьект запроса
             *
             * @var Request
             */
            public static $request;

            /**
             * Обьект ответа
             *
             * @var Response
             */
            public static $response;

            /**
             * Корень приложения
             *
             * @var string
             */
            public static $appRoot;

            /**
             * Корень web
             *
             * @var string
             */
            public static $webRoot;

            /**
             * Конфигурационный файл приложения
             *
             * @var Config
             */
            public static $config;

            /**
             * Диспатчер событий
             *
             * @var EventDispatcher
             */
            public static $eventDispatcher;

            /**
             * Менеджер модулей
             *
             * @var ModuleManager
             */
            public static $moduleManager;

            /**
             * Менеджер безопасности
             *
             * @var SecurityManager
             */
            public static $securityManager;

            /**
             * Доступ к данным DAL
             *
             * @var DataAccessPoints
             */
            public static $dataAccessPoints;
            
            /**
             * Лог девайс
             * @var Logger
             */
            public static $log;

            /**
             * Список приложений
             *
             * @var []
             */
            private static $services;

            /**
             * Закрываем конструктор
             */
            private function __construct() { 
            }

            /**
             * Статический конструктор
             *
             * @return void
             */
            public static function Create()
            {

                if(!self::$instance) {
                    self::$instance = new App();
                    self::$instance->Initialize();
                }

                return self::$instance;
            }

            /**
             * Инициализация приложения
             *
             * @return void
             */
            public function Initialize() {

                set_error_handler([$this, 'ErrorHandler']);
                set_exception_handler([$this, 'ExeptionHandler']);

                self::$services = [];
                
                // получаем местоположение приложения
                if(!self::$appRoot) {

                    // пробуем получить DOCUMENT_ROOT
                    $path = isset($_SERVER['DOCUMENT_ROOT']) ? '/'.trim($_SERVER['DOCUMENT_ROOT'], '/') : null;
                    if(!$path) {
                        // если запустились без веб-сервера то берем текущую папку
                        $path = __DIR__;
                    }

                    self::$webRoot = trim($path, '/').'/';

                    // корень приложения должен находится на уровень выше
                    $parts = explode('/', $path);
                    unset($parts[count($parts) - 1]);
                    $path = implode('/', $parts);

                    self::$appRoot = $path.'/';

                }
                
                // поднимаем конфиги
                if(!self::$config) {
                    self::$config = Config::Create(self::$appRoot.'/Config/App.xml');
                } 

                // создание DAL
                if(!self::$dataAccessPoints) {
                    self::$dataAccessPoints = DataAccessPoints::Create();
                }

                // поднимаем лог девайс
                if(!self::$log) {
                    self::$log = Logger::Create(self::$config->Query('logger'));
                }

                // в первую очеред запускаем события
                if (!self::$eventDispatcher) {
                    self::$eventDispatcher = EventDispatcher::Create();
                }

                $this->DispatchEvent(EventsContainer::AppInitializing);

                // запускаем запрос
                if(!self::$request) {
                    self::$request = Request::Create();
                }
                // запускаем ответ
                if (!self::$response) {
                    self::$response = Response::Create();
                } 

                if (!self::$moduleManager) {
                    self::$moduleManager = ModuleManager::Create();
                    self::$moduleManager->Initialize();
                }

                if (!self::$securityManager) {
                    self::$securityManager = SecurityManager::Create();
                    self::$securityManager->Initialize();
                }

                // инициируем WebServer
                $this->RegisterService(App::ApplicationWebSerwer, new Server());

                $this->DispatchEvent(EventsContainer::AppReady);

            }

            /**
             * Возвращает список прав для приложения
             *
             * @return array
             */
            public function GetPermissions() {

                $permissions = [];

                $permissions['app.load'] = 'Загрузка приложения'; 

                return $permissions;

            }

            /**
             * Обработчик исключений
             *
             * @param Exception $ex
             */
            public function ExeptionHandler($ex) {
                Debug::IOut("An error was accured: code ".$ex->getCode().' message: '.$ex->getMessage(), debug_backtrace());
            }

            /**
             * Обработчик ошибок
             *
             * @param mixed $errno
             * @param mixed $errstr
             * @param mixed $errfile
             * @param mixed $errline
             */
            public function ErrorHandler($errno, $errstr, $errfile, $errline ) {
                throw new AppException($errstr.'; file: '.$errfile.'; line: '.$errline, $errno);
            }

            /**
             * Регистрирует сервис для обработки запросов
             *
             * @param string $type Тип сервиса
             * @param Server $webServerObject сервис, наследованный от \Colibri\Web\Server
             * @return void
             */
            public function RegisterService($type, $webServerObject) {
                if(!isset(self::$services[$type])) {
                    self::$services[$type] = [];
                }
                self::$services[$type][] = $webServerObject;
            }

            /**
             * Возвращает зарегистрированный сервис по типу
             *
             * @param string $type
             * @return Server
             */
            public function GetService($type) {
                return isset(self::$services[$type]) ? reset(self::$services[$type]) : null; 
            }

            /**
             * Возвращает список зарегистрированных сервисов
             *
             * @param string $type
             * @return Server[]
             */
            public function GetServices($type) {
                return isset(self::$services[$type]) ? self::$services[$type] : []; 
            }

        }
        

        App::Create();

    }

    
