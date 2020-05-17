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

        use Colibri\Encryption\Crypt;
        use Colibri\Utils\ObjectEx;
        use Colibri\Xml\XmlNode;

        /**
         * Пользователь
         * 
         * @property-read Role $role
         */
        class User extends ObjectEx
        {

            /**
             * Конструктор
             *
             * @param mixed $userData
             */
            public function __construct($userData)
            {
                parent::__construct($userData);
                $this->role = Role::Load($this->role);

                if (isset($userData->permissions)) {
                    if (is_array($userData->permissions)) {
                        $this->permissions = $userData->permissions;
                    } else {
                        $perms = array();
                        $permissions = XMLNode::LoadNode($this->permissions);
                        $xperms = $permissions->Query('./permission');
                        foreach ($xperms as $p) {
                            $perms[$p->attributes->path->value] =  $p->attributes->value->value;
                        }

                        $this->permissions = $perms;
                    }
                } else {
                    $this->permissions = array();
                }
                $this->password = Crypt::Decrypt($this->name, $this->password);
            }

            /**
             * Загрузка пользователя
             *
             * @param string $user
             * @return User
             */
            public static function Load($user)
            {
                $users = SecurityManager::$instance->dataAdapter->Users();
                foreach ($users as $u) {
                    if ($u->id == $user || $u->name == $user) {
                        return $u;
                    }
                }

                return false;
            }

            /**
             * Авторизация
             *
             * @param string $pass
             * @return bool
             */
            public function Authorize($pass)
            {
                return $this->password == $pass;
            }

            /**
             * Сеттер
             *
             * @param string $property свойство
             * @param mixed $value значение
             */
            public function __set($property, $value)
            {
                $property = strtolower($property);
                if ($property === 'role') {
                    if ($value instanceof Role) {
                        parent::__set('role', $value);
                    } elseif (is_numeric($value)) {
                        parent::__set('role', Role::Load($value));
                    }
                }
                else {
                    parent::__set($property, $value);
                }
            }

            /**
             * Проверка разрешения на право
             *
             * @param string $command
             * @return bool
             */
            public function IsCommandAllowed($command)
            {
                $perms = $this->role->permissions;
                foreach ($this->permissions as $key => $value) {
                    $perms[$key] = $value;
                }


                $permissions = array_reverse($perms);
                foreach ($permissions as $permission => $access) {
                    $permission = str_replace('*', '.*', str_replace('.', '\.', $permission));
                    if (preg_match('/^'.$permission.'$/im', $command, $matches) > 0) {
                        return $access == 'allow';
                    }
                }
                return false;
            }

            /**
             * Сохранение пользователя
             *
             * @return void
             */
            public function Save()
            {
                $permissions = '<permissions>';
                foreach ($this->permissions as $key => $value) {
                    $permissions .= '<permission path="'.$key.'" value="'.$value.'" />';
                }
                $permissions .= '</permissions>';

                $userData = array();
                $userData['name'] = $this->name;
                $userData['fio'] = $this->fio;
                $userData['avatar'] = $this->avatar;
                $userData['password'] = Crypt::Encrypt($this->name, $this->password);
                $userData['role'] = $this->role->id;
                $userData['permissions'] = $permissions;

                $this->id = SecurityManager::$instance->dataAdapter->UpdateUser($userData, $this->id);
            }
        }

    }
