<?php

    /**
     * Shell
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2020 ColibriLab
     * @package Colibri\Utils\Shell
     * 
     */
    namespace Colibri\Utils\Shell {

        /**
         * Выполняет ассинхронно команду в shell
         */
        class Async {

            /**
             * Pid процесса
             *
             * @var integer
             */
            protected $pid = 0;
            
            /**
             * Конструктор
             *
             * @param string|Execute $command
             */
            public function __construct($command = null) {
                if ($command) {
                    $this->Run($command);
                }
            }

            /**
             * Статический конструктор
             *
             * @param string|Execute $command
             * @return Async
             */
            public function Create($command = null) {
                return new Async($command);
            }
    
            /**
             * Выполняет команду
             *
             * @param string|Execute $command
             * @return Async
             */
            public function Run($command) {
                if($command instanceof Execute) {
                    $command = $command->Command();
                    $this->pid = shell_exec("$command > /dev/null & echo $!");
                }
                else {
                    $this->pid = shell_exec("$command > /dev/null & echo $!");
                }
                return $this;
            }
            
            /**
             * Проверяет жив ли процесс
             *
             * @return bool
             */
            public function IsRunning() {
                if ($this->pid) {
                    exec('ps ' . $this->pid , $state);
                    return (count($state) >= 2);
                }
                return false;
            }
    
            /**
             * Отключает процесс
             *
             * @return bool
             */
            function Kill() {
                if ( $this->IsRunning()) {
                    exec('kill -KILL ' . $this->pid);
                    return true;
                }
                return false;
            }
        }

    }