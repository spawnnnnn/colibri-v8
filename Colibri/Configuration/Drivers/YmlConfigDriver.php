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
        use Colibri\Helpers\Objects;
        use Colibri\Helpers\Variable;
        use Colibri\Configuration\ConfigException;
        use Colibri\FileSystem\File;

        /**
         * Драйвер для хранения конфигурации в yml
         */
        class YmlConfigDriver implements IConfigDriver {

            private $_configData;

            public function __construct($configData)
            {
                if (is_array($configData) || is_object($configData)) {
                    $this->_configData = $configData;
                } else {
                    if(File::Exists($configData)) {
                        $this->_configData = \yaml_parse_file($configData);
                    }
                    else {
                        $this->_configData = \yaml_parse($configData);
                    }
                }
            }
            
            /**
             * @inheritDoc
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
                                $data = $this->_prepareValue($data[$cmdItem][$cmdIndex]);
                            } else {
                                throw new ConfigException('Illeval query');
                            }
                        } else {
                            if (!isset($data[$commandItem])) {
                                throw new ConfigException('Illeval query');
                            }
                            // не массив
                            $data = $this->_prepareValue($data[$commandItem]);
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
             * Функция обработки команд в yaml
             *
             * @param string $value значение
             * @return mixed
             */
            private function _prepareValue($value)
            {
                if (is_object($value) || is_array($value)) {
                    return $value;
                }

                $return = $value;
                if (strstr($value, 'include')) {
                    $res = preg_match('/include\((.*)\)/', $value, $matches);
                    if ($res > 0) {
                        try {
                            $return = \yaml_parse_file(App::$appRoot.'/Config/'.$matches[1]);
                        }
                        catch(\Exception $e) {
                            throw new ConfigException('An included file does not found on disk. File: '.(App::$appRoot.'/Config/'.$matches[1]));
                        }
                    } else {
                        $return = null;
                    }
                }
                return $return;
            }

            /**
             * @inheritDoc
             */
            public function Write(string $query, $value) {

            }

            /**
             * @inheritDoc
             */
            public function AsObject()
            {
                return (object)Objects::ArrayToObject($this->_configData);
            }

            /**
             * @inheritDoc
             */
            public function AsArray()
            {
                return (array)$this->_configData;
            }

            /**
             * @inheritDoc
             */
            public function AsRaw() {
                return $this->AsArray();
            }

            /**
             * @inheritDoc
             */
            public function GetValue()
            {
                if (Variable::IsAssociativeArray($this->_configData) || is_array($this->_configData)) {
                    return null;
                }
                return $this->_configData;
            }

        }

    }