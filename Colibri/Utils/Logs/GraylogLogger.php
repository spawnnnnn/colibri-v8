<?php
    
    /**
     * Logs
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2020 ColibriLab
     * @package Colibri\Utils\Logs
     * 
     */
    namespace Colibri\Utils\Logs {
        
        /**
         * Класс для работы с GrayLog
         */
        class GraylogLogger extends Logger {
            
            /**
             * @inheritDoc
             */
            public function __construct($maxLogLevel = 7, $device = '') {
                $this->_maxLogLevel = $maxLogLevel;    
                if(!is_object($device) && !is_array($device)) {
                    throw new LoggerException('Invalid device information');
                }            
                
                if(!isset($device->server) || !isset($device->port)) {
                    throw new LoggerException('Invalid device information');
                }

                $this->_device = $device;

            }
            
            /**
             * @inheritDoc
             */
            public function WriteLine($level, $data) {

                if($level > $this->_maxLogLevel) {
                    return ;
                }
                
                $gelf = [
                    'version' => '1.1',
                    'host' => $this->_device->host ?? $_SERVER['HTTP'] ?? '',
                    'short_message' => $data->description,
                    'full_message' => $data->backlog,
                    'level' => $level,
                ];
                $gelf = array_merge($gelf, (array)$data->data);
                
                $data = json_encode((object)$gelf, JSON_UNESCAPED_UNICODE);
                
                $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
                if($socket) {
                    socket_sendto($socket, $data, strlen($data), 0, $this->_device->server, $this->_device->port);
                }
                else {
                    throw new LoggerException('Не смогли создать сокен в GrayLog', 500);
                }
                
            }

            /**
             * @inheritDoc
             */
            public function Content() {
                return null;
            }
            
            
        }
        
        
        
        
    }
    
?>