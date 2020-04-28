<?php    
    /**
     * Обьект в html и обратно
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Helpers
     * 
     * 
     */
    namespace Colibri\Helpers {

        use Colibri\Xml\XmlNode;

        /**
         * Обьект в html и обратно
         */
        class HtmlEncoder {

            /**
             * Обькет в xml
             *
             * @param mixed $object
             * @param string $tag
             * @return string
             */
            public static function Encode($object, $tag = 'object') {
                if(is_string($object)) {
                    return $object;
                }
                
                $ret = ['<div class="'.$tag.'">'];
                foreach($object as $key => $value) {
                    if(is_object($value) || is_array($value)) {
                        $ret[] = HtmlEncoder::Encode($value, $key);
                    }
                    else {
                        $ret[] = '<div class="'.$key.'">'.$value.'</div>';
                    }
                }
                $ret[] = '</div>';
                return implode('', $ret);
            }

            /**
             * Строка в xml
             *
             * @param string $xmlString
             * @return SimpleXMLElement
             */
            public static function Decode($xmlString) {
                // нужно выбрать что мы используем simplexml или dom и вернуть обьект
                $xml = $xmlString instanceof XmlNode ? $xmlString : XmlNode::LoadHtmlNode($xmlString);
                if($xml->children->Count() == 0) {
                    return $xml->value;
                }

                $ret = [];
                foreach($xml->children as $child) {
                    $ret[$child->name] = HtmlEncoder::Decode($child);
                }
                return (object)$ret; 
            }

        }

    }