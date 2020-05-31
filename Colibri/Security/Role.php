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

        use Colibri\Utils\ObjectEx;
        use Colibri\Xml\XmlNode;

        /**
         * Роль пользователя
         */
        class Role extends ObjectEx
        {
            /**
             * Конструктор
             *
             * @param mixed $roleData
             */
            public function __construct($roleData)
            {
                parent::__construct($roleData);

                if (isset($roleData->permissions)) {
                    if (is_array($roleData->permissions)) {
                        $this->permissions = $roleData->permissions;
                    } else {
                        $perms = array();
                        $permissions = XmlNode::LoadNode($roleData->permissions);
                        $xperms = $permissions->Query('./permission');
                        foreach ($xperms as $p) {
                            $perms[$p->attributes->path->value] =  $p->attributes->value->value;
                        }
                        $this->permissions = $perms;
                    }
                } else {
                    $this->permissions = array();
                }
            }

            /**
             * Загрузка роли в память
             *
             * @param string $role
             * @return Role
             */
            public static function Load($role)
            {
                $roles = SecurityManager::Instance()->dataAdapter->Roles();
                foreach ($roles as $r) {
                    if ($r->id == $role) {
                        return $r;
                    }
                }
                return null;
            }

            /**
             * Список пользователей роли
             *
             * @return User[]
             */
            public function Users()
            {
                $users = array();
                $xusers = SecurityManager::Instance()->dataAdapter->Users();
                foreach ($xusers as $user) {
                    if ($user->role->id == $this->id) {
                        $users[] = $user;
                    }
                }
                return $users;
            }

            /**
             * Сохранить роль
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

                $roleData = array();
                $roleData['name'] = $this->name;
                $roleData['permissions'] = $permissions;

                $this->id = SecurityManager::Instance()->dataAdapter->UpdateRole($roleData, $this->id);
            }



            
        }

    }
