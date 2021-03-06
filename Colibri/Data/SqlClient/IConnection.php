<?php
    /**
     * SqlClient
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Data\SqlClient
     * 
     */
    namespace Colibri\Data\SqlClient {

        /**
         * Интерфейс который должны обеспечить все классы подключения точек доступа
         */
        interface IConnection
        {
            /**
             * Открытие подключения
             *
             * @return bool
             */
            public function Open();
        
            /**
             * Переоткрывает закрытое соеднинение
             *
             * @return bool
             */
            public function Reopen();
        
            /**
             * Закрывает соединение
             *
             * @return bool
             */
            public function Close();
        }

    }
?>
