<?php

    /**
     * Events
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Events
     */
    namespace Colibri\Events {

        /**
         * Базовый класс "Диспетчер событий"
         */
        trait TEventDispatcher
        {

            /**
             * Поднять событие
             *
             * @param string $event
             * @param mixed $args
             * @return mixed
             */
            public function DispatchEvent($event, $args = null)
            {
                return EventDispatcher::Instance()->Dispatch(new Event($this, $event), $args);
            }
        
            /**
             * Добавить обработчик события
             *
             * @param string $ename
             * @param mixed $listener
             * @return mixed
             */
            public function HandleEvent($ename, $listener)
            {
                EventDispatcher::Instance()->AddEventListener($ename, $listener, $this);
                return $this;
            }
        
            /**
             * Удалить обработчик события
             *
             * @param string $ename
             * @param mixed $listener
             * @return mixed
             */
            public function RemoveHandler($ename, $listener)
            {
                EventDispatcher::Instance()->RemoveEventListener($ename, $listener);
                return $this;
            }
        }

    }