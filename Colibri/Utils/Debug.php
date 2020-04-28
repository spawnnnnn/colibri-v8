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
             * Функция вывода
             *
             * @return void
             */
            public static function Out() {

                $mode = App::$config ? App::$config->Query('mode')->GetValue() : 'development';
                if($mode === App::ModeTest) {
                    App::$log->WriteLine(Logger::Error, func_get_args());
                }
                else if($mode === App::ModeDevelopment) {

                    $args = func_get_args();
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
                    echo "<pre>\n" . str_replace("<", "&lt;", str_replace(">", "&gt;", implode(" : ", $result)))  . "\n</pre>";
                    if(isset($_SERVER['argv'])) {
                        try { ob_flush(); } catch(\Exception $e) {  }
                    }

                }
            }

            /**
             * Печатает красиво обьект в виде дерева
             *
             * @param mixed $what
             */
            public static function IOut($what){

                $mode = App::$config ? App::$config->Query('mode')->GetValue() : 'development';
                if($mode === App::ModeTest) {
                    App::$log->WriteLine(Logger::Error, func_get_args());
                }
                else if($mode === App::ModeDevelopment) {

                    $args = func_get_args();
                    $count = count($args);
                    $result = array();
                    for ($i = 0; $i < $count; $i++){
                        switch (gettype($args[$i])){
                            case "boolean" :
                                $result[] = !$args[$i] ? 'FALSE' : 'TRUE';
                                break;
                            case "NULL" :
                                $result[] = "NULL";
                                break;
                            default :
                                $result[] = print_r($args[$i], true);
                        }
                    }

                    $result = print_r($result, true);
                    $result = str_replace("<", "&lt;", str_replace(">", "&gt;", $result));

                    $clickevent = "onclick='javascript: iout_toggle(event);'";
                    $result = preg_replace("/\s*?\[(.*)\] \=&gt; (.*?)\n/mi", "\n<div class='legend' ".$clickevent.">[\$1] => \$2</div>\n", $result);
                    $result = preg_replace("/(<div class='legend' ".preg_quote($clickevent).">.*<\/div>)\n\s*?\(/mi", "\n<div class='object'><div class='hilite'>\$1</div><div class='children' style='display: none'>\n", $result);
                    $result = preg_replace("/\n\s*?\)\n/", "\n</div></div>\n", $result);
                    $result = preg_replace("/Array\n\(\n/i", "\n<div class='result'><div class='object'><div class='legend' ".$clickevent.">IOUT - Result</div><div class='children'>\n", $result);

                    echo '
                        <style type="text/css">
                            div.legend {
                                cursor: default;
                                cursor: expression("hand");
                                padding-top: 2px;
                                padding-bottom: 2px;
                            }

                            div.legend span {
                                margin-left: 5px;
                            }

                            div.object {
                                font-size: 12px;
                                font-family: courier new;
                            }
                            div.children {
                                margin-left: 50px;
                                padding-top: 1px;
                                padding-bottom: 1px;
                                border-left: 1px solid #f9f9f9;
                                min-height: 5px;
                                height: expression("5px");
                            }
                            div.result {
                                border: 1px solid #f2f2f2;
                                padding: 10px;
                            }
                            div.hilite {
                                color: #050;
                            }
                        </style>

                        <script language="javascript">
                            function iout_toggle(e) {
                                var parent = null;
                                if(e.srcElement)
                                    parent = e.srcElement.parentElement;
                                else {
                                    parent = e.currentTarget.parentNode;
                                }
                                if(parent.className == "hilite") {
                                    if(e.srcElement)
                                        parent = parent.parentElement;
                                    else
                                        parent = parent.parentNode;
                                    var children = parent.childNodes[1];
                                    children.style.display = children.style.display == "" ? "none" : "";
                                }
                            }
                        </script>
                    '.$result.'</div>';
                }
            }

        }

    }