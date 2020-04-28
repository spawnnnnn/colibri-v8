<?php

    /**
     * Kласс обработчика Controller
     * 
     * @author Ваган Григорян <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Web
     * @version 1.0.0
     * 
     */
    namespace Colibri\Web {

        use Colibri\Helpers\Strings;

        /**
         * Абстрактный класс для обработки Web запросов
         * 
         * Наследуемся от него и создаем функцию, которую можно будет вызвать
         * например: 
         * 
         * запрос: 
         * /buh/web/page/method1.html
         * /buh/web/page/method1.json
         * /buh/web/page/method1.xml
         * 
         * namespace App\Transformers\Buh\Web
         * 
         * class PageController extends Colibri\Web\Controller {
         * 
         *      public function Method1($get, $post, $payload) {
         *          
         *          тут пишем что нужно и финишируем функцией Finish
         * 
         *          внимание:
         *          $get, $post, $payload - изменять бессмысленно, так как это копии данных переданных в запросе
         *          
         *          ЗАПРЕЩАЕТСЯ:
         *          1. выводить что либо с помощью функции echo, print_r, var_dump и т.д.
         *          2. запрашивать другие RPC Handler-ы на том же уровне
         *          3. реализовывать бизнес-логику в классе-обработчике (наследники RpcHandler) 
         *          
         *          $code = 200 | 400 и т.д.
         *          $message = какое либо сообщение
         *          $result = peyload ответа, может быть строкой в случае с html/xml 
         * 
         *          ! НИКАКОГО ECHO !!!! ЗАПРЕЩЕНО
         * 
         *          пример результата:
         * 
         *          div => [
         *              span => тестт
         *          ]
         * 
         *          xml хелпер создаст:
         * 
         *          <div><span>тестт</span></div>
         *          
         *          html хелпер создаст:
         * 
         *          <div class="div"><div class="span">тестт</div></div>
         * 
         *  
         *          return $this->Finish(int $code, string $message, mixed $result);         
         * 
         *      }
         * 
         * 
         * }
         * 
         */
        class Transformer {

            /**
             * Обьект Сервер
             *
             * @var Server
             */
            protected $_server;

            public function __construct(Server $server)
            {
                $this->_server = $server;
            }

            /**
             * Завершает работу обработчика
             *
             * @param int $code код ошибки
             * @param string $message сообщение
             * @param mixed $result дополнительные параметры
             * @return stdClass готовый результат
             */
            public function CreateResultObject($code, $message, $result = null) {
                $res = (object)[];
                $res->code = $code;
                $res->message = $message;
                $res->result = $result;
                return $res;
            }  

            /**
             * Создаем ссылку для добавления в url
             *
             * @param string $method название функции в классе контроллера 
             * @param string $type тип возвращаемого значения: json, xml, html
             * @return string
             */
            public static function GetEntryPoint($method, $type) {
                $class = static::class;

                // если контроллер в модуле
                if(strpos($class, 'App\\Modules\\') === 0) {
                    $class = str_replace('App\\', '', $class);
                    $class = str_replace('Transformers\\', '', $class);
                }
                else {
                    $class = str_replace('App\\Transformers\\', '', $class);
                }

                $class = str_replace('\\', '/', $class);
                $class = substr($class, 0, -1 * strlen('Transformer'));
                $parts = explode('/', $class);
                $newParts = [];
                foreach($parts as $c) {
                    $newParts[] = Strings::FromCamelCaseAttr($c);
                }
                return implode('/', $newParts).'/'.Strings::FromCamelCaseAttr($method).'.'.$type;
            }

        }

    }