<?php
    /**
     * FileSystem
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\FileSystem
     */
    namespace Colibri\IO\FileSystem {

        use Colibri\AppException;
        use Colibri\Collections\ICollection;

        /**
         * Свойства безопасности файловой системы
         * 
         * @property boolean $denied
         * @property boolean $grant
         * @property boolean $read
         * @property boolean $write
         * @property boolean $delete
         * @property boolean $execute
         * @property string $owner
         * 
         */
        class Security {

            protected $source;
            protected $flags;

            function __construct($source, $flags = null){
                $this->source = $source;
                if ($flags === null) {
                    return;
                }

                if ($flags instanceof ICollection) {
                    $this->flags = $flags->rawArray;
                }
                else if (is_array($flags)) {
                    $this->flags = $flags;
                }
                else {
                    throw new AppException('illegal arguments: ' . __CLASS__);
                }
            }

            function __get($property) {
                return $this->flags->$property;
            }

            function __set($property, $value){
                $this->flags->$property = $value;
            }

        }

    }