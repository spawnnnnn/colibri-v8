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

        /**
         * Интерфейс для адаптера данных пользователей и ролей
         */
        interface IDataAdapter
        {
            /**
             * Проверить необходимость установки
             *
             * @return bool
             */
            public function Check();

            /**
             * Создать источники данных
             *
             * @return void
             */
            public function Create();

            /**
             * Удаление источников данных
             *
             * @return void
             */
            public function Dispose();

            /**
             * Возвращает список пользователей
             *
             * @return User[]
             */
            public function Users();

            /**
             * Возвращает список ролей
             *
             * @return Role[]
             */
            public function Roles();

            /**
             * Обновляет данные роли
             *
             * @param Role $roleData
             * @param int $id
             * @return int
             */
            public function UpdateRole($roleData, $id = null);

            /**
             * Обновляет данные пользователя
             *
             * @param User $userData
             * @param int $id
             * @return int
             */
            public function UpdateUser($userData, $id = null);
        }

    }