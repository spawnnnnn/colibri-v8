<?php
    /**
     * Modules
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Modules
     */
    namespace Colibri\Modules {

        use Colibri\Events\TEventDispatcher;
        use Colibri\Configuration\Config;
        use Colibri\FileSystem\File;
        use ReflectionClass;

        /**
         * Модуль
         * базовый класс
         *
         * @property-read string $modulePath
         */
        class Module
        {

            // подключаем trait событийной модели
            use TEventDispatcher;
            
            /**
             * Обьект настроек
             *
             * @var Colibri\Utils\Config
             */
            protected $_config;

            /**
             * Местоположение модуля
             *
             * @var string
             */
            protected $_modulePath;

            /**
             * Путь к файлу модуля
             *
             * @var string
             */
            protected $_moduleFile;
            
            public function __construct()
            {
                // загружаем настройки модуля
                $staticClass = new ReflectionClass($this);
                $this->_moduleFile = $staticClass->getFilename();
                $this->_modulePath = dirname($this->_moduleFile).'/';
                $this->_config = null;
                if (File::Exists($this->_modulePath.'Config/Module.xml')) {
                    $this->_config = Config::Create($this->_modulePath.'Config/Module.xml');
                }
            }

            /**
             * Возвращает обьект кофигурации
             *
             * @return Config
             */
            public function Config()
            {
                return $this->_config;
            }

            public function __get($prop)
            {
                $prop = strtolower($prop);
                if ($prop == 'modulepath') {
                    return $this->_modulePath;
                }
                return false;
            }
            
            /**
             * Инициализация, вызывается после создания обьекта модуля
             *
             * @return void
             */
            public function InitializeModule()
            {
            }

            /**
             * Удаление обьекта модуля
             *
             * @return void
             */
            public function Dispose()
            {
            }

            /**
             * Список прав модуля, стандартный набор
             *
             * @return array
             */
            public function GetPermissions()
            {
                $permissions = [];

                $className = static::class;
                $permissionsName = strtolower(str_replace('\\', '.', $className));

                $permissions[ $permissionsName . '.load' ] = 'Загрузка модуля';
                $permissions[ $permissionsName . '.install' ] = 'Установка модуля';
                $permissions[ $permissionsName . '.uninstall' ] = 'Деинсталляция модуля';

                return $permissions;
            }
        }

    }
