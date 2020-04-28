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
        use Colibri\IO\FileSystem\File;
        use Colibri\Security\SecurityException;
        use Colibri\IO\FileSystem\Directory;
        use Colibri\Security\Role;
        use Colibri\Security\User;
        use Colibri\Xml\XmlNode;

        /**
         * Адаптер данных с хранением в XML
         */
        class XmlDataAdapter implements IDataAdapter
        {
            private $_dataPoint;
            private $_sourceUsers;
            private $_sourceRoles;

            public function __construct($datapoint, $usersTable, $rolesTable)
            {
                $this->_dataPoint = $datapoint;
                $this->_sourceUsers = $usersTable;
                $this->_sourceRoles = $rolesTable;
            }

            /**
             * @inheritDoc
             */
            public function Check()
            {
                try {
                    if (!Directory::Exists(App::$appRoot.'Config/'.$this->_dataPoint) || !File::Exists(App::$appRoot.'Config/'.$this->_dataPoint.'users.xml') || !File::Exists(App::$appRoot.'Config/'.$this->_dataPoint.'roles.xml')) {
                        return false;
                    }
                } catch (SecurityException $e) {
                    return false;
                }
                return true;
            }

            /**
             * @inheritDoc
             */
            public function Create()
            {
                Directory::Create(App::$appRoot.'Config/'.$this->_dataPoint, true, 0777);
                File::Create(App::$appRoot.'Config/'.$this->_dataPoint.'users.xml', true, 0777);
                File::Create(App::$appRoot.'Config/'.$this->_dataPoint.'roles.xml', true, 0777);

                File::Write(App::$appRoot.'Config/'.$this->_dataPoint.'/'.$this->_sourceUsers.'.xml', '<'.'?xml version="1.0" encoding="utf-8"?'.'>
                <users>
                    <row>
                        <id>1</id>
                        <name>admin</name>
                        <password>4b6dP/8=</password>
                        <fio>Администратор</fio>
                        <avatar></avatar>
                        <role>1</role>
                        <permissions><![CDATA[<permissions></permissions>]]></permissions>
                    </row>
                </users>
                ');

                File::Write(App::$appRoot.'Config/'.$this->_dataPoint.'/'.$this->_sourceRoles.'.xml', '<'.'?xml version="1.0" encoding="utf-8"?'.'>
                <roles>
                    <row>
                        <id>1</id>
                        <name>Administrator</name>
                        <permissions><![CDATA[
                            <permissions><permission path="*" value="allow" /></permissions>
                        ]]></permissions>
                    </row>
                    <row>
                        <id>2</id>
                        <name>Readonly</name>
                        <permissions><![CDATA[
                            <permissions><permission path="*" value="deny" /><permission path="security.login" value="allow" /></permissions>
                        ]]></permissions>
                    </row>
                    <row>
                        <id>3</id>
                        <name>Disabled</name>
                        <permissions><![CDATA[
                            <permissions><permission path="*" value="deny" /></permissions>
                        ]]></permissions>
                    </row>
                    <row>
                        <id>4</id>
                        <name>Manager</name>
                        <permissions><![CDATA[
                            <permissions><permission path="*" value="deny" /><permission path="login" value="allow" /><permission path="*access" value="allow" /><permission path="*.data.*" value="allow" /></permissions>
                        ]]></permissions>
                    </row>
                </roles>
                ');
            }

            /**
             * @inheritDoc
             */
            public function Dispose()
            {
                // do nothing
            }

            /**
             * @inheritDoc
             */
            public function Users()
            {
                $users = array();

                $xml = XmlNode::Load(App::$appRoot.'Config/'.$this->_dataPoint.'/'.$this->_sourceUsers.'.xml', true);
                $rows = $xml->Query('//row');
                foreach ($rows as $row) {
                    $u = (object)[
                        'id' => $row->id->value,
                        'name' => $row->Item('name')->value,
                        'password' => $row->password->value,
                        'fio' => $row->fio->value,
                        'avatar' => $row->avatar->value,
                        'role' => $row->role->value,
                        'permissions' => $row->permissions->value,
                    ];
                    $users[] = new User($u);
                }
                return $users;
            }

            /**
             * @inheritDoc
             */
            public function Roles()
            {
                $roles = array();
                $xml = XMLNode::Load(App::$appRoot.'Config/'.$this->_dataPoint.'/'.$this->_sourceRoles.'.xml', true);
                $rows = $xml->Query('//row');
                foreach ($rows as $row) {
                    $r = (object)[
                        'id' => $row->id->value,
                        'name' => $row->Item('name')->value,
                        'permissions' => $row->permissions->value,
                    ];
                    $roles[] = new Role($r);
                }
                return $roles;
            }

            /**
             * @inheritDoc
             */
            public function UpdateRole($roleData, $id = null)
            {
                $path = App::$appRoot.'Config/'.$this->_dataPoint.'/'.$this->_sourceRoles.'.xml';
                $xml = XMLNode::Load($path, true);
                if ($id) {
                    // update
                    $role = $xml->Query('//row/id[text()="'.$id.'"]')->first->parent;
                    foreach ($roleData as $key => $value) {
                        $role->Item($key)->value = $value;
                    }
                    $xml->Save($path);
                    return $role->Item('id')->value;
                } else {
                    $maxId = $xml->Query('//row/id')->last->value + 1;
                    $xml->Append('<row><id>'.$maxId.'</id><name>'.$roleData->name.'</name><permissions><![CDATA['.$roleData->permissions.']]></permissions></row>');
                    $xml->Save($path);
                    return $maxId;
                }
            }

            /**
             * @inheritDoc
             */
            public function UpdateUser($userData, $id = null)
            {
                $userData = (object)$userData;
                $path = App::$appRoot.'Config/'.$this->_dataPoint.'/'.$this->_sourceUsers.'.xml';
                $xml = XMLNode::Load($path, true);
                if ($id) {
                    // update
                    $role = $xml->Query('//row/id[text()="'.$id.'"]')->first->parent;
                    foreach ($userData as $key => $value) {
                        $role->Item($key)->value = $value;
                    }
                    $xml->Save($path);
                    return $role->Item('id')->value;
                } else {
                    $maxId = $xml->Query('//row/id')->last->value + 1;
                    $xml->Append('<row>
                        <id>'.($maxId).'</id>
                        <name>'.$userData->name.'</name>
                        <password>'.$userData->password.'</password>
                        <fio>'.$userData->fio.'</fio>
                        <avatar>'.$userData->avatar.'</avatar>
                        <role>'.$userData->role.'</role>
                        <permissions><![CDATA['.$userData->permissions.']]></permissions>
                    </row>');
                    $xml->Save($path);
                    return $maxId;
                }
            }
        }

    }