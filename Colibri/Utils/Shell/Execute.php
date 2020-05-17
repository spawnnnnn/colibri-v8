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
         * Выполняет набор команд от определенного пользователя, имеет возможность выгрузить результат в файл
         * Есть встроенная статическая функция для выполнения одиночныx команд
         */
        class Execute {

            /**
             * Внутренний массив команд
             *
             * @var string[]
             */
            private $_commands;

            /**
             * Скрипт, выполняющий команду под нужным пользователем
             * нужно для ограничения пользователя www-data в режиме sudo
             *
             * @var string
             */
            private $_sudoProcessor = '/usr/local/bin/shell-batch';

            /**
             * Конструктор
             * 
             * получает на вход путь к файлу sudoProcessor
             * в частности в нем должно быть следующее
             * sudo su <user|root> -c "$1"
             * файл должен быть с правами 07хх для пользователя www-data
             * в /etc/sudoers должно быть разрешено www-data исполнять этот файл
             *
             * @param string $sudoProcessor путь к файлу, к которому разрешен доступ из веб-скриптов, и который умеет выполнять команды 
             * @return void
             */
            public function __construct($sudoProcessor = null) {
                $this->_sudoProcessor = $sudoProcessor;
                $this->_commands = array();
            }

            /**
             * Добавляет команду в набор
             *
             * @param string $command
             */
            public function Add($command) {
                $this->_commands[] = $command;
            }

            /**
             * Возвращает сформированную команду shell
             *
             * @return string
             */
            public function Command() {

                $command = 'sudo '.$this->_sudoProcessor.' "';
                foreach($this->_commands as $cmd) {
                    $command .= str_replace('"', '\"', $cmd)." && ";
                }
                return $command.'"';

            }

            /**
             * Выполняет набор команд, если при создании класса передавали путь к файлу, 
             * то вернет FALSE, если нет тогда результат вывода набора команд
             */
            public function Exec() {

                $command = $this->Command();
                if($this->_outputFile) {
                    system($command);
                }
                else {
                    return shell_exec($command.' 2>&1');
                }

            }

            /**
             * Статическая функция выполняющая комманду
             *
             * @param string $sudoProcessor скрипт, выполняющий команды под нужным пользователем
             * @param string $command команда для выполнения
             * @param string $outputFile файл, куда отправить вывод
             */
            public static function ExecNow($sudoProcessor, $command, $outputFile = false) {

                $command = 'sudo '.$sudoProcessor.' "'.str_replace("\"", "\\\"", $command).'"'.($outputFile ? ' > '.$outputFile : ' 2>&1');
                if($outputFile){
                    system($command);
                }
                else{
                    return shell_exec($command);
                }
            }


        }

    }