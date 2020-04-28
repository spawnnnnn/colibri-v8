<?php    
    /**
     * Обьект в xml и обратно
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Helpers
     * 
     * 
     */
    namespace Colibri\Helpers {

        use Colibri\Utils\Debug;
        use Colibri\Xml\XmlNode;

        /**
         * Обьект в xml и обратно
         */
        class XmlEncoder {

            private static function _getContent($v, $cdata) {
                $ret = '';
                if(Variable::IsObject($v) || Variable::IsArray($v)){
                    $ret = XmlEncoder::Encode($v);
                }
                else if(Variable::IsBool($v) || ($v == 't' || $v == 'f')){
                    $ret =  ($v || $v == 't' ? 'true' : 'false');
                }
                else if(!Variable::IsNull($v) && !Variable::IsEmpty($v)) {
                    $ret =  $cdata ? '<![CDATA['.$v.']]>' : $v;
                }
                return $ret;
            }
    
            public static function Encode($object, $tagName = null, $cdata = true) {
                           
                $ret = '';
                if($tagName){
                    $ret .= '<'.$tagName.'>';
                }
                
                foreach($object as $k => $v) {
                    
                    $ret .= Variable::IsNumeric($k) ? '<object index="'.$k.'">' : '<'.$k.'>';
                    $ret .= self::_getContent($v, $cdata);
                    $ret .= Variable::IsNumeric($k) ? '</object>' : '</'.$k.'>';
                    
                }
                            
                if($tagName){
                    $ret .= '</'.$tagName.'>';
                }
                
                return $ret;
            }

            /**
             * Строка в xml
             *
             * @param string $xmlString
             * @return SimpleXMLElement
             */
            public static function Decode($xmlString) {
                // нужно выбрать что мы используем simplexml или dom и вернуть обьект
                $xml = $xmlString instanceof XmlNode ? $xmlString : XmlNode::LoadNode($xmlString);
                if($xml->children->Count() == 0) {
                    return $xml->attributes->value ? $xml->attributes->value->value : $xml->value;
                }

                $ret = [];
                foreach($xml->children as $child) {
                    $key = $child->attributes->name ? $child->attributes->name->value : $child->name;
                    if(!isset($ret[$key])) {
                        $ret[$key] = [];
                    }
                    $ret[$key][] = XmlEncoder::Decode($child);
                }

                foreach($ret as $k => $v) {
                    if(count($v) == 1) {
                        $ret[$k] = reset($v);
                    }
                }

                return (object)$ret; 
            }

        }

    }