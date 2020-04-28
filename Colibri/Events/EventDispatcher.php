<?php

    /**
     * Events
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Events
     */
    namespace Colibri\Events {

        use Colibri\Collections\ArrayList;
        use Colibri\Collections\Collection;

        /**
         * Менеджер событий
         *
         */
        class EventDispatcher
        {
        
            /**
             * Синглтон
             *
             * @var EventDispatcher
             */
            public static $instance;
        
            /**
             * Массив событий
             *
             * @var Collection
             */
            private $_events;
        
            private function __construct()
            {
                $this->_events = new Collection();
            }
        
            /**
             * Статический конструктор
             *
             * @return EventDispatcher
             */
            public static function Create()
            {
                if (!self::$instance) {
                    self::$instance = new self();
                }
                return self::$instance;
            }
        
            /**
             * Удаляет обьект
             *
             * @return void
             */
            public function Dispose()
            {
                $this->_events->Clear();
            }
        
            public function __clone()
            {
            }
        
            /**
             * Добавляет обработчик события
             *
             * @param string $ename
             * @param mixed $listener
             * @param mixed $object
             * @return boolean
             */
            public function AddEventListener($ename, $listener = '', $object = null)
            {
                // если не передали listener
                // или если передали обьект и listener не строка
                // то выходим
                if (empty($listener) || (!is_object($object) && !is_string($listener) && !is_callable($listener))) {
                    return false;
                }

                $minfo = $listener;
                if (is_object($object)) {
                    $minfo = (object)[];
                    $minfo->listener = $listener;
                    $minfo->object = $object;
                }

                $e = $this->_events->$ename;
                if (is_null($e)) {
                    $e = new ArrayList();
                    $this->_events->Add($ename, $e);
                }

                if (!$e->Contains($minfo)) {
                    $e->Add($minfo);
                    return true;
                }

                return false;
            }
        
            /**
             * Удаляет обработчик события
             *
             * @param string $ename
             * @param mixed $listener
             * @return void
             */
            public function RemoveEventListener($ename, $listener)
            {
                if (!$this->_events->Exists($ename)) {
                    return false;
                }
            
                $e = $this->_events->$ename;
                if ($e == null) {
                    return false;
                }
            
                return $e->Delete($listener);
            }
        
            /**
             * Поднять событие
             *
             * @param string $event
             * @param mixed $args
             * @return void
             */
            public function Dispatch($event, $args = null)
            {
                if (!($event instanceof Event) || !$this->_events->Exists($event->name)) {
                    return false;
                }

                $e = $this->_events->Item($event->name);
                if ($e == null) {
                    return false;
                }
            
                foreach ($e as $item) {
                    if (is_callable($item)) {
                        $result = $item($event, $args);
                    } elseif (is_object($item)) {
                        $object = $item->object;
                        $listener = $item->listener;
                        if($listener instanceof \Closure) {
                            $newListener = \Closure::bind($listener, $object);
                            $result = $newListener($event, $args);
                        } elseif (method_exists($object, strval($listener))) {
                            $result = $object->$listener($event, $args);
                        }
                    } elseif (function_exists(strval($item))) {
                        $result = $item($event, $args);
                    }
                
                    if ($result === false) {
                        break;
                    }
                }
            
                return $args;
            }
        
            /**
             * Проверяет наличие обработчика на событие
             *
             * @param string $ename
             * @param mixed $listener
             * @return void
             */
            public function HasEventListener($ename, $listener)
            {
                if (!$this->_events->Exists($ename)) {
                    return false;
                }
            
                $e = $this->_events->$ename;
                if ($e == null) {
                    return false;
                }
            
                return $e->Exists($listener);
            }
        
            public function RegisteredListeners($ename = "")
            {
                if ($this->_events->Count() == 0) {
                    return false;
                }
                
                $listeners = new ArrayList();
                if (empty($ename)) {
                    foreach ($this->_events as $listeners) {
                        $listeners->Append($listeners);
                    }
                } else {
                    if ($this->_events->Exists($ename)) {
                        $listeners->Append($this->_events->$ename);
                    }
                }

                return $listeners;
            }
        }

    }
