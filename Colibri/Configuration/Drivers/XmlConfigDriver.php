<?php

    /**
     * Configuration
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Configuration\Drivers
     *
     */
    namespace Colibri\Configuration\Drivers {

        use Colibri\App;
        use Colibri\Collections\ArrayList;
        use Colibri\Helpers\XmlEncoder;
        use Colibri\Configuration\ConfigException;
        use Colibri\IO\FileSystem\File;
        use Colibri\Xml\XmlNode;

        /**
         * Драйвер для хранения конфигурации в xml
         */
        class XmlConfigDriver implements IConfigDriver {

            /**
             * Данные конфиграционного файла
             *
             * @var XmlNode
             */
            private $_configData;

            /**
             * Путь к файлу, если это файл
             *
             * @var string
             */
            private $_path;
            
            /**
             * Конструктор
             *
             * @param mixed $configData
             */
            public function __construct($configData)
            {
                $this->_path = null;
                if ($configData instanceof XmlNode) {
                    $this->_configData = $configData;
                } else {
                    try {
                        if(File::Exists($configData)) {
                            $f = new File($configData);
                            $this->_path = $f->directory->path;
                            $this->_configData = XmlNode::Load($configData, true);
                        }
                        else {
                            $this->_configData = XmlNode::LoadNode($configData);
                        }
                    }
                    catch(\Exception $e) {
                        throw new ConfigException('Invalid value for configuration');
                    }
                }
            }
            
            /**
             * Считает данные
             *
             * @param string $query
             * @param mixed $default
             * @return mixed
             */
            public function Read(string $query, $default = null) {
                $command = explode('.', $query);

                try {
                    $data = $this->_configData;
                    foreach ($command as $commandItem) {
                        if (strstr($commandItem, '[') !== false) {
                            // массив
                            $res = preg_match('/(.+)\[(\d+)\]/', $commandItem, $matches);
                            if ($res > 0) {
                                
                                $cmdItem = $matches[1];
                                $cmdIndex = $matches[2];
                                $res = $data->Query('./'.$cmdItem)->ItemAt($cmdIndex);
                                if(!$res) {
                                    throw new ConfigException('Illeval query');
                                }
                                $data = $this->_prepareValue($res);

                            } else {
                                throw new ConfigException('Illeval query');
                            }
                        } else {
                            $res = $data->Query('./'.$commandItem);
                            if ($res->Count() == 0) {
                                throw new ConfigException('Illeval query');
                            }
                            // не массив
                            if($res->Count() == 1) {
                                $data = $this->_prepareValue($res->First());
                            }
                            else {
                                $ret = [];
                                foreach($res as $node) {
                                    $ret[] = $this->_prepareValue($node);
                                }
                                $data = $ret;
                            }
                        }
                    }
                } catch (ConfigException $e) {
                    if ($default) {
                        $data = $default;
                    } else {
                        throw $e;
                    }
                }

                return $data;

            }

            /**
             * Функция обработки команд в xml
             *
             * @param string $value значение
             * @return mixed
             */
            private function _prepareValue(XmlNode $value)
            {
                if($value->attributes->include && $value->attributes->include->value) {
                    return XMLNode::Load(($this->_path ? $this->_path : App::AppRoot()).$value->attributes->include->value, true);
                }
                return $value;
            }

            /**
             * Пишет данные
             *
             * @param string $query
             * @param mixed $value
             * @return bool
             */
            public function Write(string $query, $value) {

            } 
            
            /**
             * Вернуть внутренние данные в виде обьекта
             *
             * @return stdClass
             */
            public function AsObject() {
                return (object)XmlEncoder::Decode($this->_configData->xml);
            }

            /**
             * Вернуть внутренние данные в виде массива
             *
             * @return array
             */
            public function AsArray() {
                return (array)XmlEncoder::Decode($this->_configData->raw);
            }

            /**
             * Вернуть внутренние данные в исходном виде
             *
             * @return array
             */
            public function AsRaw() {
                return $this->_configData;
            }

            /**
             * Вернуть хранимое значение
             * Внимание! Если текущие данные массив или обьект, то будет возвращен null
             *
             * @return mixed
             */
            public function GetValue() {
                if ($this->_configData instanceof XmlNode) {
                    $value = $this->_configData->attributes->value ? $this->_configData->attributes->value->value : $this->_configData->value;
                    if(in_array($value, ['true', 'false'])) {
                        // Это логическое значение
                        $value = $value == 'true';
                    }
                    else if(is_numeric($value)) {
                        $value = strstr('.', $value) !== false ? (float)$value : (int)$value;
                    }
                    return $value;
                }
                return null;
            }
            
        }

    }