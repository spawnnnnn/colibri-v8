<?php

    /**
     * Utils
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2020 ColibriLab
     * @package Colibri\Utils
     * 
     */
    namespace Colibri\Utils {

        use Colibri\App;
        use Colibri\Utils\Logs\Logger;

        /**
         * Методы для вывода отладочной информации
         */
        class Debug {

            /**
             * Превращает аргументы в правильный текстовый вид
             *
             * @param array $args
             * @return array
             */
            private static function _createArgs($args) {
                $count = count($args);
                $result = array();
                for ($i = 0; $i < $count; $i++){
                    switch (gettype($args[$i])){
                        case "boolean" :
                            $result[] = $args[$i] ? 'true' : 'false';
                            break;
                        case "NULL" :
                            $result[] = "NULL";
                            break;
                        default :
                            $result[] = print_r($args[$i], true);
                    }
                }
                return $result;
            } 

            /**
             * Функция вывода
             *
             * @return void
             */
            public static function Out() {

                try {
                    $mode = App::Config()->Query('mode')->GetValue();
                }
                catch(\Exception $e) {
                    $mode = App::ModeDevelopment;
                }

                if($mode === App::ModeTest) {
                    App::Logger()->WriteLine(Logger::Error, func_get_args());
                }
                else if($mode === App::ModeDevelopment) {
                    $result = self::_createArgs(func_get_args());
                    echo "<pre>\n" . str_replace("<", "&lt;", str_replace(">", "&gt;", implode(" : ", $result)))  . "\n</pre>";
                    if(isset($_SERVER['argv'])) {
                        try { ob_flush(); } catch(\Exception $e) {  }
                    }
                }
            }

            /**
             * Печатает красиво обьект в виде дерева
             *
             */
            public static function IOut(){

                $mode = App::Config() ? App::Config()->Query('mode')->GetValue() : 'development';
                if($mode === App::ModeTest) {
                    App::Logger()->WriteLine(Logger::Error, func_get_args());
                }
                else if($mode === App::ModeDevelopment) {
                    $clickevent = 'onclick="event.currentTarget.parentElement.nextElementSibling && (event.currentTarget.parentElement.nextElementSibling.style.display = event.currentTarget.parentElement.nextElementSibling.style.display == \'\' ? \'none\' : \'\');"';
                    $result = self::_createArgs(func_get_args());
                    $result = print_r($result, true);
                    $result = str_replace("<", "&lt;", str_replace(">", "&gt;", $result));
                    $result = preg_replace("/\s*?\[(.*)\] \=&gt; (.*?)\n/mi", "\n<div class='legend' ".$clickevent.">[\$1] => \$2</div>\n", $result);
                    $result = preg_replace("/(<div class='legend' ".preg_quote($clickevent).">.*<\/div>)\n\s*?\(/mi", "\n<div class='object'><div class='hilite'>\$1</div><div class='children' style='display: none'>\n", $result);
                    $result = preg_replace("/\n\s*?\)\n/", "\n</div></div>\n", $result);
                    $result = preg_replace("/Array\n\(\n/i", "\n<div class='result'><div class='object'><div class='legend' ".$clickevent.">IOUT - Result</div><div class='children'>\n", $result).'</div>';
                    echo $result.'<style type="text/css">div.result { border: 1px solid #f2f2f2; padding: 10px;} div.legend { cursor: pointer; padding: 2px 0; } div.object { font: 12px monospace; } div.children { padding: 1px 0 1px 50px; border-left: 1px solid #f9f9f9; min-height: 5px; } div.hilite { color: #050; }</style>';
                }
            }

        }

    }