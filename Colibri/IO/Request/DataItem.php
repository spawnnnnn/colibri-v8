<?php
    /**
     * Request
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Request
     */
    namespace Colibri\IO\Request {

        use Colibri\Utils\ObjectEx;

        /**
         * Строка данных в запросе
         * @property string $name
         * @property string $value
         */
        class DataItem extends ObjectEx
        {
            public function __construct($name, $data)
            {
                parent::__construct();
                $this->name = $name;
                $this->value = $data;
            }
        }
    }
