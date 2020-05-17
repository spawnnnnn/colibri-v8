<?php

    /**
     * Security
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2020 ColibriLab
     * @package Colibri\Security\Adapters
     * 
     */
    namespace Colibri\Security\Adapters {

        use Colibri\App;
        use Colibri\Data\DataAccessPoint;
        use Colibri\Encryption\Rc4Crypt;
        use Colibri\Security\Role;
        use Colibri\Security\SecurityException;
        use Colibri\Security\User;

        /**
         * Класс адаптера для работы с базой данных
         */
        class DataAdapter implements IDataAdapter
        {
            /**
             * Точка доступа
             *
             * @var DataAccessPoint
             */
            private $_dataPoint;
            /**
             * Таблица пользователей
             *
             * @var string
             */
            private $_sourceUsers;
            /**
             * Таблица ролей
             *
             * @var string
             */
            private $_sourceRoles;

            /**
             * КОнструктор
             *
             * @param string $datapoint
             * @param string $usersTable
             * @param string $rolesTable
             */
            public function __construct($datapoint, $usersTable, $rolesTable)
            {
                $this->_dataPoint = App::$dataAccessPoints->Get($datapoint);
                $this->_sourceUsers = $usersTable;
                $this->_sourceRoles = $rolesTable;
            }

            /**
             * Проверить необходимость установки
             *
             * @return bool
             */
            public function Check()
            {
                try {
                    $this->_dataPoint->Query('select * from '.$this->_sourceRoles.' limit 1');
                } catch (SecurityException $e) {
                    return false;
                }
                return true;
            }

            /**
             * Создать источники данных
             *
             * @return void
             */
            public function Create()
            {
                try {
                    $this->_dataPoint->Query('
                        CREATE TABLE `'.$this->_sourceUsers.'` (
                        `id` bigint(20) NOT NULL AUTO_INCREMENT,
                        `name` varchar(255) DEFAULT NULL,
                        `password` varchar(255) DEFAULT NULL,
                        `fio` varchar(255) DEFAULT NULL,
                        `avatar` varchar(255) DEFAULT NULL,
                        `role` bigint(255) DEFAULT NULL,
                        `permissions` longtext,
                        PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                    ', ['type' => DataAccessPoint::QueryTypeNonInfo]);
                    $this->_dataPoint->Query('
                        CREATE TABLE `'.$this->_sourceRoles.'` (
                        `id` bigint(20) NOT NULL AUTO_INCREMENT,
                        `name` varchar(255) DEFAULT NULL,
                        `permissions` longtext,
                        PRIMARY KEY (`id`),
                        KEY `'.$this->_sourceRoles.'_name` (`name`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                    ', ['type' => DataAccessPoint::QueryTypeNonInfo]);
                    $this->_dataPoint->Query('insert into `'.$this->_sourceRoles.'`(id,name,permissions) values(1,\'Administrator\',\'<permissions><permission path="*" value="allow" /></permissions>\')', ['type' => DataAccessPoint::QueryTypeNonInfo]);
                    $this->_dataPoint->Query('insert into `'.$this->_sourceRoles.'`(id,name,permissions) values(2,\'Readonly\',\'<permissions><permission path="*" value="deny" /><permission path="security.login" value="allow" /></permissions>\')', ['type' => DataAccessPoint::QueryTypeNonInfo]);
                    $this->_dataPoint->Query('insert into `'.$this->_sourceRoles.'`(id,name,permissions) values(3,\'Disabled\',\'<permissions><permission path="*" value="deny" /></permissions>\')', ['type' => DataAccessPoint::QueryTypeNonInfo]);
                    $this->_dataPoint->Query('insert into `'.$this->_sourceRoles.'`(id,name,permissions) values(4,\'Manager\',\'<permissions><permission path="*" value="deny" /><permission path="login" value="allow" /><permission path="*access" value="allow" /><permission path="*.data.*" value="allow" /></permissions>\')', ['type' => DataAccessPoint::QueryTypeNonInfo]);
                    $this->_dataPoint->Query('insert into `'.$this->_sourceRoles.'`(id,name,password,fio,avatar,role,permissions) values(1,\'admin\',\''.Rc4Crypt::Encrypt('admin', 'admin').'\',\'Администратор\',\'\',\'1\',\'<permissions></permissions>\')', ['type' => DataAccessPoint::QueryTypeNonInfo]);
                } catch (SecurityException $e) {
                    return false;
                }
                return true;
            }

            /**
             * Удаление источников данных
             *
             * @return void
             */
            public function Dispose()
            {
                // do nothing
                return true;
            }

            /**
             * Возвращает список пользователей
             *
             * @return User[]
             */
            public function Users()
            {
                $users = array();
                $reader = $this->_dataPoint->Query('select * from '.$this->_sourceUsers);
                while ($u = $reader->Read()) {
                    $users[] = new User($u);
                }
                return $users;
            }

            /**
             * Возвращает список ролей
             *
             * @return Role[]
             */
            public function Roles()
            {
                $roles = array();
                $reader = $this->_dataPoint->Query('select * from '.$this->_sourceRoles);
                while ($r = $reader->Read()) {
                    $roles[] = new Role($r);
                }
                return $roles;
            }

            /**
             * Обновляет данные роли
             *
             * @param Role $roleData
             * @param int $id
             * @return int
             */
            public function UpdateRole($roleData, $id = false)
            {
                if ($id) {
                    // update
                    return ($this->_dataPoint->Update($this->_sourceRoles, $roleData, 'id=\''.$this->id.'\'') === true ? $id : false);
                } else {
                    $res = $this->_dataPoint->Insert($this->_sourceRoles, $roleData, 'id');
                    if ($res->insertid > -1) {
                        return $res->insertid;
                    } else {
                        return false;
                    }
                }
            }

            /**
             * Обновляет данные пользователя
             *
             * @param User $userData
             * @param int $id
             * @return int
             */
            public function UpdateUser($userData, $id = false)
            {
                if ($id) {
                    // update
                    $result = $this->_dataPoint->Update($this->_sourceUsers, $userData, 'id=\''.$id.'\'');
                    if ($result->error != '') {
                        return false;
                    }
                    return $id;
                } else {
                    $res = $this->_dataPoint->Insert($this->_sourceUsers, $userData, 'id');
                    if ($res->insertid > -1) {
                        return $res->insertid;
                    } else {
                        return false;
                    }
                }
            }
            
        }

    }