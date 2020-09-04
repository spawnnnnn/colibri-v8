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
        use Psr\Container\ContainerInterface;
        use Psr\Container\NotFoundExceptionInterface;

        /**
         * Класс приложения
         */
        final class App implements ContainerInterface
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
             * Контейнер для хранения всякого
             *
             * @var array
             */
            private $_containedDIObjects;

            /**
             * Синглтон
             *
             * @var App
             */
            private static $instance;

            /**
             * Корень приложения
             *
             * @var string
             */
            private static $appRoot;

            /**
             * Корень web
             *
             * @var string
             */
            private static $webRoot;

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
                // Do nothing: Закрываенм конструктор
            }

            /**
             * Статический конструктор
             *
             * @return App
             */
            public static function Instance()
            {

                if(!self::$instance) {
                    self::$instance = new self();
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

                    self::$webRoot = '/'.trim($path, '/').'/';

                    // корень приложения должен находится на уровень выше
                    $parts = explode('/', $path);
                    unset($parts[count($parts) - 1]);
                    $path = implode('/', $parts);

                    self::$appRoot = $path.'/';

                }
                
                // поднимаем конфиги
                if(!$this->Has(Config::class)) {
                    $this->_containedDIObjects[Config::class] = Config::Create(self::$appRoot.'/Config/App.xml');
                } 

                // создание DAL
                if(!$this->Has(DataAccessPoints::class)) {
                    $this->_containedDIObjects[DataAccessPoints::class] = DataAccessPoints::Instance();
                }

                // поднимаем лог девайс
                if(!$this->Has(Logger::class)) {
                    $this->_containedDIObjects[Logger::class] = Logger::Create(self::Config()->Query('logger'));
                }

                // в первую очеред запускаем события
                if (!$this->Has(EventDispatcher::class)) {
                    $this->_containedDIObjects[EventDispatcher::class] = EventDispatcher::Instance();
                }

                $this->DispatchEvent(EventsContainer::AppInitializing);

                // запускаем запрос
                if(!$this->Has(Request::class)) {
                    $this->_containedDIObjects[Request::class] = Request::Instance();
                }
                // запускаем ответ
                if (!$this->Has(Response::class)) {
                    $this->_containedDIObjects[Response::class] = Response::Instance();
                } 

                if (!$this->Has(ModuleManager::class)) {
                    $moduleManager = ModuleManager::Instance();
                    $moduleManager->Initialize();
                    $this->_containedDIObjects[ModuleManager::class] = $moduleManager;

                }

                if (!$this->Has(SecurityManager::class)) {
                    $securityManager = SecurityManager::Instance();
                    $securityManager->Initialize();
                    $this->_containedDIObjects[SecurityManager::class] = $securityManager;
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
                Debug::IOut("An error was accured: code ".$ex->GetCode().' message: '.$ex->GetMessage(), debug_backtrace());
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


            /**
             * Finds an entry of the container by its identifier and returns it.
             *
             * @param string $id Identifier of the entry to look for.
             *
             * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
             * @throws ContainerExceptionInterface Error while retrieving the entry.
             *
             * @return mixed Entry.
             */
            public function Get($id) {
                if(!$this->Has($id)) {
                    return null;
                }
                return $this->_containedDIObjects[$id];
            }

            /**
             * Returns true if the container can return an entry for the given identifier.
             * Returns false otherwise.
             *
             * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
             * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
             *
             * @param string $id Identifier of the entry to look for.
             *
             * @return bool
             */
            public function Has($id) {
                return isset($this->_containedDIObjects[$id]);
            }

            /**
             * Возвращает полный путь к приложению
             * @return string путь к приложению
             */
            public static function AppRoot() {
                return self::$appRoot;
            }

            /**
             * Возвращает полный путь к корню вебсайта
             * @return string путь к корню вебсайта
             */
            public static function WebRoot() {
                return self::$webRoot;
            }

            /**
             * Возвращает конфигурацию приложения
             *
             * @return Config
             */
            public static function Config() {
                return self::Instance()->Get(Config::class);
            }

            /**
             * Возвращает контейнер подключений
             *
             * @return DataAccessPoints
             */
            public static function DataAccessPoints() {
                return self::Instance()->Get(DataAccessPoints::class);
            }

            /**
             * Возвращает менеджер событий
             *
             * @return EventDispatcher
             */
            public static function EventDispatcher() {
                return self::Instance()->Get(EventDispatcher::class);
            }

            /**
             * Возвращает логгер
             *
             * @return Logger
             */
            public static function Logger() {
                return self::Instance()->Get(Logger::class);
            }

            /**
             * Возвращает серверный запрос
             *
             * @return Request
             */
            public static function Request() {
                return self::Instance()->Get(Request::class);
            }

            /**
             * Возвращает обьект для ответа серверу
             *
             * @return Response
             */
            public static function Response() {
                return self::Instance()->Get(Response::class);
            }

            /**
             * Возвращает менеджер модулей
             *
             * @return ModuleManager
             */
            public static function ModuleManager() {
                return self::Instance()->Get(ModuleManager::class);
            }

            /**
             * Возвращает менеджер безопасности
             *
             * @return SecurityManager
             */
            public static function SecurityManager() {
                return self::Instance()->Get(SecurityManager::class);
            }

        }
        

        App::Instance();

    }

    
