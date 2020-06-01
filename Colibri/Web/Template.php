<?php
    /**
     * Web
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Web
     * 
     * 
     */
    namespace Colibri\Web {

        use Colibri\Events\EventsContainer;
        use Colibri\AppException;
        use Colibri\Events\TEventDispatcher;
        use Colibri\IO\FileSystem\File;
        use Colibri\Utils\ExtendedObject;

        /**
         * Класс шаблона
         * 
         * @property-read string $file
         * 
         */
        class Template {

            use TEventDispatcher;

            /**
             * Путь к файлу шаблона
             *
             * @var string
             */
            private $_file;

            /**
             * Конструктор
             *
             * @param string $file файл шаблона
             */
            public function __construct($file) {

                $this->_file = $file.'.layout';
                if(!File::Exists($this->_file)) {
                    throw new AppException('Unknown template');
                }

            }

            /**
             * Вывод шаблона
             *
             * @param mixed $args
             * @return string
             */
            public function Render($args = null) {
                $template = $this;

                $args = new ExtendedObject($args);

                $this->DispatchEvent(EventsContainer::TemplateRendering, array('template' => $this, 'args' => $args));

                ob_start();

                require($this->_file); 

                $ret = ob_get_contents();
                ob_end_clean();

                $this->DispatchEvent(EventsContainer::TemplateRendered, array('template' => $this, 'content' => $ret));

                return $ret;

            }

            /**
             * Статический конструктор
             *
             * @param string $file
             * @return Template
             */
            public static function Create($file) {
                return new Template($file);
            }

            /**
             * Get
             *
             * @param string $prop
             * @return mixed
             */
            public function __get($prop) {
                if(strtolower($prop) == 'file') {
                    return $this->_file;
                }
                throw new AppException('Unknown property');
            }

            /**
             * Замена вставок в шаблон
             *
             * @param string $code код для выполнения
             * @param ExtendedObject $args аргументы для передачи в код
             * @return void
             */
            public static function Run($code, ExtendedObject $args) {
                return preg_replace_callback('/\{\?\=(.*?)\?\}/', function($match) use ($args) {
                    return eval('return '.html_entity_decode($match[1]).';');
                }, $code);
            }

        }


    }
