<?php

    /**
     * Security
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2020 ColibriLab
     * @package Colibri\Security
     * 
     */
    namespace Colibri\Security {

        use Colibri\App;
        use Colibri\Events\TEventDispatcher;
        use Colibri\Events\EventsContainer;

        /**
         * Класс управления правами и пользователями
         * 
         * @property-read IDataAdapter $dataAdapter
         */
        class SecurityManager
        {
            use TEventDispatcher;

            /**
             * Синглтон
             *
             * @var SecurityManager
             */
            private static $instance;

            /**
             * Список прав
             *
             * @var array
             */
            private $_permissions;

            /**
             * Дерево прав
             *
             * @var array
             */
            private $_permissionsTree;

            /**
             * Адаптер данных
             *
             * @var IDataAdapter
             */
            private $_dataAdapter;

            /**
             * Конструктор
             */
            public function __construct()
            {
                $this->_permissions = [];
                $this->_permissionsTree = [];

                $dataAdapterClass = 'Colibri\\Security\\Adapters\\'.App::Config()->Query('security.data-adapter')->GetValue();
                $this->_dataAdapter = new $dataAdapterClass(
                    App::Config()->Query('security.access-point')->GetValue(), 
                    App::Config()->Query('security.users-source')->GetValue(), 
                    App::Config()->Query('security.roles-source')->GetValue()
                );

            }

            /**
             * Статичесий конструктор
             *
             * @return SecurityManager
             */
            public static function Instance()
            {
                if (!self::$instance) {
                    self::$instance = new self();
                }

                return self::$instance;
            }

            /**
             * Возвращает список прав для SecurityManager
             *
             * @return array
             */
            public function GetPermissions() {
                $permissions = [];

                $permissions['app.security.manage'] = 'Управление безопасностью';
                $permissions['app.security.roles.list'] = 'Отображение списка ролей';
                $permissions['app.security.roles.add'] = 'Добавление роли';
                $permissions['app.security.roles.remove'] = 'Удаление роли';
                $permissions['app.security.roles.edit'] = 'Редактировние роли';
                $permissions['app.security.users.list'] = 'Отображение списка пользователей';
                $permissions['app.security.users.add'] = 'Добавление пользователя';
                $permissions['app.security.users.remove'] = 'Удаление пользователя';
                $permissions['app.security.users.edit'] = 'Редактирование пользователя';

                return $permissions;
            }

            /**
             * Инициализация модуля
             *
             * @return void
             */
            public function Initialize()
            {
                
                $this->Install();
                
                // подключение прав приложения
                if(method_exists(App::Instance(), 'GetPermissions')) {
                    $this->_permissions = array_merge($this->_permissions, App::Instance()->GetPermissions());
                }

                // подключение прав безопасности
                if(method_exists($this, 'GetPermissions')) {
                    $this->_permissions = array_merge($this->_permissions, $this->GetPermissions());
                }

                if(method_exists(App::ModuleManager(), 'GetPermissions')) {
                    $this->_permissions = array_merge($this->_permissions, App::ModuleManager()->GetPermissions());
                }

                // подключение прав модулей
                foreach(App::ModuleManager()->list as $module) {
                    if(method_exists($module, 'GetPermissions')) {
                        $this->_permissions = array_merge($this->_permissions, $module->GetPermissions());
                    }
                }

                $this->_permissionsTree = $this->CreatePermissionsTree();

                $this->DispatchEvent(EventsContainer::SecurityManagerReady);
            }

            /**
             * Создает дерево прав для управления
             *
             * @return array
             */
            public function CreatePermissionsTree() {

                $tree = [];
                foreach($this->_permissions as $permission => $description) {
    
                    $permission = explode('.', $permission);
                    $evalCode = '$tree';
                    foreach($permission as $p) {
                        $evalCode .= '["'.$p.'"]';
                    }
                    $evalCode .= '="'.$description.'";';
                    eval($evalCode);
    
                }
                return $tree;
    
            }

            /**
             * Установка модуля
             *
             * @return void
             */
            public function Install()
            {
                return $this->_dataAdapter->Create();
            }

            /**
             * Удаление модуля
             *
             * @return void
             */
            public function Uninstall()
            {
                // nothing to do
                return $this->_dataAdapter->Dispose();
            }

            /**
             * Геттер
             *
             * @param string $name
             * @return mixed
             */
            public function __get($name)
            {
                if(strtolower($name) == 'dataadapter') {
                    return $this->_dataAdapter;
                }
                return null;
            }

        }


    }
