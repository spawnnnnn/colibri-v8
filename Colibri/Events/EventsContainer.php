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
         * Контейер для событий
         * от этого класс должен быть наследован класс EventsContainer в Colibri\App
         * 
         * в ядре используется этот контенйер, в приложении Colibri\App\EventsContainer
         * 
         */
        class EventsContainer
        {

            #region Application events

            /**
             * Срабатывает после завершения инициализации приложения
             * без параметров
             */
            const AppReady = 'app.ready';

            /**
             * Начало инициализации
             * без параметров
             */
            const AppInitializing = 'app.initializing';

            #endregion

            #region Request events

            /**
             * Когда готов обьект Request
             * без параметров
             */
            const RequestReady = 'request.ready';

            #endregion

            #region Request events

            /**
             * Когда готов обьект Response
             * без параметров
             */
            const ResponseReady = 'response.ready';

            #endregion

            #region ModuleManager events 

            /**
             * Срабатывает после завершения загрузки всех модулей
             * без параметров
             */
            const ModuleManagerReady = 'modulemanager.ready';

            #endregion

            #region SecurityManager events 

            /**
             * Срабатывает после завершения загрузки всех модулей
             * без параметров
             */
            const SecurityManagerReady = 'securitymanager.ready';

            #endregion

            #region Assets

            /**
             * Начало компиляции Assets
             * параметры: string $type, string $name, string[] $blocks
             * используемая часть результата string[] $blocks
             */
            const AssetsCompiling = 'assets.compiling';

            /**
             * Компиляция assets завершена
             * параметры: string $type, string $name, string $cacheUrl
             * результат не используется
             */
            const AssetsCompiled = 'assets.compiled';

            #endregion

            #region Server

            /**
             * Получен запрос RPC
             * параметры: string $class, string $method, stdClass $get, stdClass $post, stdClass $payload
             * результат: boolean $cancel, stdClass $result
             */
            const ServerGotRequest = 'server.request';

            /**
             * Запрос выполнен
             * параметры: mixed $object, string $method, stdClass $get, stdClass $post, stdClass $payload
             * результат не используется
             */
            const ServerRequestProcessed = 'server.complete';

            /**
             * Получен запрос RPC
             * параметры: string $class, string $method, stdClass $get, stdClass $post, stdClass $payload, string $message
             * результат: boolean $cancel, stdClass $result
             */
            const ServerRequestError = 'server.error';

            #endregion

            #region Template

            /**
             * Шаблон обрабатывается
             * параметры: Template $template, ObjectEx $args
             * результат не используется
             */
            const TemplateRendering = 'template.rendering';

            /**
             * Шаблон обработан
             * параметры: Template $template, string $content
             * результат не используется
             */
            const TemplateRendered = 'template.rendered';

            #endregion

        }
    }