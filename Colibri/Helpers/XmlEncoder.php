<?php
    /**
     * Helpers
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Helpers
     */
    namespace Colibri\Helpers {

        use Colibri\Xml\XmlNode;

        /**
         * Обьект в xml и обратно
         */
        class XmlEncoder
        {
            /**
             * Возвращает контент обьекта в виде xml
             *
             * @param mixed $v
             * @param bool $cdata
             * @return string
             */
            private static function _getContent($v, $cdata = false)
            {
                $ret = '';
                if (Variable::IsObject($v) || Variable::IsArray($v)) {
                    $ret = XmlEncoder::Encode($v, null, $cdata);
                } elseif (Variable::IsBool($v) || ($v == 't' || $v == 'f')) {
                    $ret =  ($v || $v == 't' ? 'true' : 'false');
                } elseif (!Variable::IsNull($v) && !Variable::IsEmpty($v)) {
                    $ret =  $cdata ? '<![CDATA['.$v.']]>' : $v;
                }
                return $ret;
            }
    
            /**
             * Обьект в xml строку
             *
             * @param mixed $object обьект для кодирования
             * @param string $tagName тэг в который нужно запаковать данные
             * @param boolean $cdata использовать CDATA для хранения данных
             * @return string строка XML
             */
            public static function Encode($object, $tagName = null, $cdata = true)
            {
                $ret = '';
                if ($tagName) {
                    $ret .= '<'.$tagName.'>';
                }
                
                foreach ($object as $k => $v) {
                    $ret .= Variable::IsNumeric($k) ? '<object index="'.$k.'">' : '<'.$k.'>';
                    $ret .= self::_getContent($v, $cdata);
                    $ret .= Variable::IsNumeric($k) ? '</object>' : '</'.$k.'>';
                }
                            
                if ($tagName) {
                    $ret .= '</'.$tagName.'>';
                }
                
                return $ret;
            }

            /**
             * XML/XML строка в обьект
             *
             * @param mixed $xmlString строка для кодирования
             * @return stdClass результирующий обьект
             */
            public static function Decode($xmlString)
            {
                $xml = $xmlString instanceof XmlNode ? $xmlString : XmlNode::LoadNode($xmlString);
                if ($xml->children->Count() == 0) {
                    return $xml->attributes->value ? $xml->attributes->value->value : $xml->value;
                }

                $ret = [];
                foreach ($xml->children as $child) {
                    $key = $child->attributes->name ? $child->attributes->name->value : $child->name;
                    if (!isset($ret[$key])) {
                        $ret[$key] = [];
                    }
                    $ret[$key][] = XmlEncoder::Decode($child);
                }

                foreach ($ret as $k => $v) {
                    if (count($v) == 1) {
                        $ret[$k] = reset($v);
                    }
                }

                return (object)$ret;
            }
        }

    }
