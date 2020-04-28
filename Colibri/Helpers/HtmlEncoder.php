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
         * Обьект в html и обратно
         */
        class HtmlEncoder
        {

            /**
             * Обькет в xml
             *
             * @param mixed $object объект для кодирования
             * @param string $tag тэг, который нужно вернуть
             * @return string
             */
            public static function Encode($object, $tag = 'object')
            {
                if (is_string($object)) {
                    return $object;
                }
                
                $ret = ['<div class="'.$tag.'">'];
                foreach ($object as $key => $value) {
                    if (is_object($value) || is_array($value)) {
                        $ret[] = HtmlEncoder::Encode($value, $key);
                    } else {
                        $ret[] = '<div class="'.$key.'">'.$value.'</div>';
                    }
                }
                $ret[] = '</div>';
                return implode('', $ret);
            }

            /**
             * XML/XML строку в обьект
             *
             * @param mixed $xmlString
             * @return stdClass
             */
            public static function Decode($xmlString)
            {
                $xml = $xmlString instanceof XmlNode ? $xmlString : XmlNode::LoadHtmlNode($xmlString);
                if ($xml->children->Count() == 0) {
                    return $xml->value;
                }

                $ret = [];
                foreach ($xml->children as $child) {
                    $ret[$child->name] = HtmlEncoder::Decode($child);
                }
                return (object)$ret;
            }
        }

    }
