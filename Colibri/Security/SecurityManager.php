<?php

    namespace Colibri\Security {

        use Colibri\App;
        use Colibri\Events\TEventDispatcher;
        use Colibri\Events\EventsContainer;
    use Colibri\Utils\Debug;

class SecurityManager
        {
            use TEventDispatcher;

            public static $instance;

            private $_permissions;
            private $_permissionsTree;

            public function __construct()
            {
                $this->_permissions = [];
                $this->_permissionsTree = [];
            }

            public static function Create()
            {
                if (!self::$instance) {
                    self::$instance = new self();
                }

                return self::$instance;
            }

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

            public function Initialize()
            {
                

                // подключение прав приложения
                if(method_exists(App::$instance, 'GetPermissions')) {
                    $this->_permissions = array_merge($this->_permissions, App::$instance->GetPermissions());
                }

                // подключение прав безопасности
                if(method_exists($this, 'GetPermissions')) {
                    $this->_permissions = array_merge($this->_permissions, $this->GetPermissions());
                }

                if(method_exists(App::$moduleManager, 'GetPermissions')) {
                    $this->_permissions = array_merge($this->_permissions, App::$moduleManager->GetPermissions());
                }

                // подключение прав модулей
                foreach(App::$moduleManager->list as $module) {
                    if(method_exists($module, 'GetPermissions')) {
                        $this->_permissions = array_merge($this->_permissions, $module->GetPermissions());
                    }
                }

                $this->_permissionsTree = $this->CreatePermissionsTree();

                $this->DispatchEvent(EventsContainer::SecurityManagerReady);
            }

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

        }


    }
