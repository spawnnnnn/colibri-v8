<?php

    namespace Colibri\Web {

        use Colibri\Events\EventsContainer;
        use Colibri\AppException;
        use Colibri\Events\TEventDispatcher;
        use Colibri\FileSystem\File;
        use Colibri\Utils\ObjectEx;

        /**
         * Класс шаблона
         * 
         * @property-read string $file
         * 
         */
        class Template {

            use TEventDispatcher;

            private $_file;

            /**
             * Конструктор
             *
             * @param Layout $layout
             * @param string $file
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

                $args = new ObjectEx($args);

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
             * @param string $code
             * @param ObjectEx $args
             * @return void
             */
            public static function Eval($code, ObjectEx $args) {
                return preg_replace_callback('/\{\?\=(.*?)\?\}/', function($match) use ($args) {
                    return eval('return '.html_entity_decode($match[1]).';');
                }, $code);
            }

        }


    }
