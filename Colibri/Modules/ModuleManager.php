<?php
    /**
     * Modules
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Modules
     */
    namespace Colibri\Modules {

        use Colibri\App;
        use Colibri\Collections\Collection;
        use Colibri\Configuration\Config;
        use Colibri\Events\TEventDispatcher;
        use Colibri\Events\EventsContainer;

        /**
         * Менеджер модулей
         *4
         * @property-read Config $settings
         * @property-read Collection $list
         *
         */
        class ModuleManager
        {

            // подключаем функционал событийной модели
            use TEventDispatcher;

            /**
             * Синглтон
             *
             * @var ModuleManager
             */
            protected static $instance;

            /**
             * Настройки
             *
             * @var stdClass
             */
            private $_settings;

            /**
             * Список модулей
             *
             * @var Collection
             */
            private $_list;

            /**
             * Конструктор
             */
            public function __construct()
            {
                $this->_list = new Collection();
            }

            /**
             * Статический конструктор
             *
             * @return ModuleManager
             */
            public static function Instance()
            {
                if (!self::$instance) {
                    self::$instance = new self();
                }
                return self::$instance;
            }

            /**
             * Инициализация менеджера
             *
             * @return void
             */
            public function Initialize()
            {
                $this->_settings = App::Config()->Query('modules');
                $entities = $this->_settings->Query('module');
                foreach ($entities as $moduleConfig) {
                    if (!$moduleConfig->Query('enabled', true)->GetValue()) {
                        continue;
                    }
                    $module = $this->InitModule($moduleConfig);
                    if ($module) {
                        $moduleName = $moduleConfig->Query('name')->GetValue();
                        $this->_list->Add($moduleName, $module);
                    }
                }

                $this->DispatchEvent(EventsContainer::ModuleManagerReady);
            }

            /**
             * Инициализирит модуль
             *
             * @param Config $configNode
             * @return Module
             */
            public function InitModule(Config $configNode)
            {
                $moduleEntry = $configNode->Query('entry')->GetValue();
                $className = '\\App\\Modules'.$moduleEntry;
                if (!class_exists($className)) {
                    return false;
                }

                $module = $className::Create();
                $module->InitializeModule();
            
                return $module;
            }

            /**
             * Геттер
             *
             * @param string $property свойство
             * @return mixed
             */
            public function __get($property)
            {
                $property = strtolower($property);
                switch ($property) {
                    case 'settings':
                        return $this->_settings;
                    case 'list':
                        return $this->_list;
                    default:
                        return $this->_list->$property;
                }
            }

            /**
             * Получает конфигурацию модуля
             *
             * @param string $name
             * @return void
             */
            public function Config($name)
            {
                return $this->_settings->$name->config;
            }

            /**
             * Список прав модуля, стандартный набор
             *
             * @return array
             */
            public function GetPermissions()
            {
                return [];
            }
        }
    }
