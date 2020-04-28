<?php

    /**
     * Драйвер для MySql
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Utils\Config
     * 
     *
     */
    namespace Colibri\Data\MySql {

        use Colibri\Data\SqlClient\IConnection;
        use Colibri\Data\MySql\Exception as MySqlException;

        /**
         * Класс подключения к базе данных MySql
         * 
         * @property-read resource $resource
         * @property-read resource $raw
         * @property-read resource $connection
         * @property-read bool $isAlive
         * 
         */
        final class Connection implements IConnection
        {
            private $_connectioninfo = null;
            private $_resource = null;
            
            /**
             * Создает обьект
             *
             * @param string $host
             * @param string $port
             * @param string $user
             * @param string $password
             * @param string $database
             */
            public function __construct($host, $port, $user, $password, $database = null)
            {
                $this->_connectioninfo = (object)[
                    'host' => $host, 
                    'port' => $port, 
                    'user' => $user, 
                    'password' => $password, 
                    'database' => $database
                ];
            }
            
            /**
             * Открывает подключения
             *
             * @return bool
             */
            public function Open()
            {
                
                if (is_null($this->_connectioninfo)) {
                    throw new MySqlException('You must provide a connection info object while creating a connection.');
                }
                
                $this->_resource = mysqli_connect($this->_connectioninfo->host, $this->_connectioninfo->user, $this->_connectioninfo->password);
                if (!$this->_resource) {
                    throw new MySqlException(mysqli_error($this->_resource));
                }
                
                if (!empty($this->_connectioninfo->database) && !mysqli_select_db($this->_resource, $this->_connectioninfo->database)) {
                    throw new MySqlException(mysqli_error($this->_resource));
                }
                
                mysqli_query($this->_resource, 'set names utf8');

                return true;
            }
            
            /**
             * Переорктывает подключение
             *
             * @return void
             */
            public function Reopen()
            {
                return $this->Open();
            }
            
            /**
             * Закрывает подключение
             *
             * @return void
             */
            public function Close()
            {
                if (is_resource($this->_resource)) {
                    mysqli_close($this->_resource);
                }
            }
            
            /**
             * Геттер
             *
             * @param string $property
             * @return mixed
             */
            public function __get($property)
            {
                switch (strtolower($property)) {
                    case "resource":
                    case "raw":
                    case "connection":
                        return $this->_resource;
                    case "isAlive":
                        return mysqli_ping($this->_resource);
                    default:
                        return null;
                }
            }
        }
    }

?>
