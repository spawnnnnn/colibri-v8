<?php

    namespace Colibri\Xml {

        use Colibri\AppException;
        use Colibri\FileSystem\File;
        use Colibri\Xml\Serialization\XmlCData;
        use Colibri\Xml\Serialization\XmlSerialized;

        /**
         * Класс работы с XML объектом
         * 
         * @property-read string $type
         * @property string $value
         * @property-read string $name
         * @property-read string $data
         * @property-read string $encoding
         * @property-read \XmlNodeAttributeList $attributes
         * @property-read \XmlNode $root
         * @property-read \XmlNode $parent
         * @property-read \XmlNodeList $nodes
         * @property-read \XmlNode $firstChild
         * @property-read \XmlNodeList $elements
         * @property-read \XmlNodeList $children
         * @property-read \XmlNodeList $texts
         * @property \DOMDocument $document
         * @property \DOMNode $raw
         * @property-read string $xml
         * @property-read string $innerXml
         * @property-read string $html
         * @property-read string $innerHtml
         * @property-read XmlNode $next
         * @property-read XmlNode $prev
         * @property-write string $cdata
         * 
         */
        class XmlNode {

            /**
             * Raw обьект документа
             *
             * @var \DOMDocument
             */
            private $_document;

            /**
             * Raw обьект элемента
             *
             * @var \DOMNode
             */
            private $_node;

            /**
             * Конструктор
             *
             * @param \DOMNode $node
             * @param \DOMDocument $dom
             */
            public function __construct(\DOMNode $node, \DOMDocument $dom = null) {
                $this->_node = $node;
                $this->_document = $dom;
            }

            /**
             * Создает обьект XmlNode из строки или файла
             *
             * @param string $xmlFile
             * @param boolean $isFile
             * @return XmlNode
             */
            public static function Load($xmlFile, $isFile = true) {
                $dom = new \DOMDocument();
                if(!$isFile){
                    $dom->loadXML($xmlFile);
                }
                else {
                    if(File::Exists($xmlFile)){
                        $dom->load($xmlFile);
                    }
                    else{
                        throw new AppException('File '.$xmlFile.' does not exists');
                    }
                }

                return new XmlNode($dom->documentElement, $dom);
            }

            /**
             *  Создает XmlNode из неполного документа
             *
             * @param string $xmlString
             * @param string $encoding
             * @return XmlNode
             */
            public static function LoadNode($xmlString, $encoding = 'utf-8') {
                $dom = new \DOMDocument('1.0', $encoding);
                $dom->loadXML((strstr($xmlString, '<'.'?xml') === false ? '<'.'?xml version="1.0" encoding="'.$encoding.'"?'.'>' : '').$xmlString);
                return new XmlNode($dom->documentElement, $dom);
            }

            /**
             *  Создает XMLHtmlNode из неполного документа
             *
             * @param string $xmlString
             * @param string $encoding
             * @return XmlNode
             */
            public static function LoadHtmlNode($xmlString, $encoding = 'utf-8') {
                $dom = new \DOMDocument('1.0', $encoding);
                $dom->loadHTML((strstr($xmlString, '<'.'?xml') === false ? '<'.'?xml version="1.0" encoding="'.$encoding.'"?'.'>' : '').$xmlString);
                return new XmlNode($dom->documentElement, $dom);
            }

            /**
             * Создает обьект XmlNode из строки или файла html
             *
             * @param string $htmlFile
             * @param boolean $isFile
             * @return XmlNode
             */
            public static function LoadHTML($htmlFile, $isFile = true, $encoding = 'utf-8') {
                libxml_use_internal_errors(true);

                $dom = new \DOMDocument('1.0', $encoding);
                if(!$isFile){
                    $dom->loadHTML($htmlFile);
                }
                else {
                    if(File::Exists($htmlFile)){
                        $dom->loadHTMLFile($htmlFile);
                    }
                    else{
                        throw new AppException('File '.$htmlFile.' does not exists');
                    }
                }

                return new XmlNode($dom->documentElement, $dom);
            }

            /**
             * Сохраняет в файл или возвращает строку XML хранящуюся в обьекте
             *
             * @param string $filename
             * @return mixed
             */
            public function Save($filename = "") {
                if(!empty($filename)) {
                    $this->_document->formatOutput = true;
                    $this->_document->save($filename, LIBXML_NOEMPTYTAG);
                }
                else {
                    return $this->_document->saveXML(null, LIBXML_NOEMPTYTAG);
                }
            }

            /**
             * Сохраняет в файл или возвращает строку HTML хранящуюся в обьекте
             *
             * @param string $filename
             * @return mixed
             */
            public function SaveHTML($filename = "") {
                if(!empty($filename)){
                    $this->_document->saveHTMLFile($filename);
                }
                else{
                    return $this->_document->saveHTML();
                }
            }

            /**
             * Getter
             *
             * @param string $property
             * @return mixed
             */
            public function __get($property) {
                switch(strtolower($property)) {
                    case 'type': {
                        return $this->_node->nodeType;
                    }
                    case 'value': {
                        return $this->_node->nodeValue;
                    }
                    case 'iscdata': {
                        return $this->_node->firstChild instanceof \DOMCdataSection;
                    }
                    case 'name': {
                        return $this->_node->nodeName;
                    }
                    case 'data': {
                        return $this->_node->data;
                    }
                    case 'encoding': {
                        return $this->_document->encoding ? $this->_document->encoding : 'utf-8';
                    }
                    case 'attributes': {
                        if(!is_null($this->_node->attributes)){
                            return new XmlNodeAttributeList($this->_document, $this->_node, $this->_node->attributes);
                        }
                        else{
                            return null;
                        }
                    }
                    case 'root': {
                        return $this->_document ? new XmlNode($this->_document->documentElement, $this->_document) : null;
                    }
                    case 'parent': {
                        return $this->_node->parentNode ? new XmlNode($this->_node->parentNode, $this->_document) : null;
                    }
                    case 'nodes': {
                        if($this->_node->childNodes){
                            return new XmlNodeList($this->_node->childNodes, $this->_document);
                        }
                        else{
                            return null;
                        }
                    }
                    case 'firstchild': {
                        return $this->_node->firstChild ? new XmlNode($this->_node->firstChild, $this->_document) : null;
                    }
                    case 'elements': {
                        return $this->Query('./child::*', true);
                    }
                    case 'children': {
                        return $this->Query('./child::*');
                    }
                    case 'texts': {
                        return $this->Query('./child::text()');
                    }
                    case 'document': {
                        return $this->_document;
                    }
                    case 'raw': {
                        return $this->_node;
                    }
                    case 'xml': {
                        return $this->_document->saveXML($this->_node, LIBXML_NOEMPTYTAG);
                    }
                    case 'innerxml': {
                        $data = $this->_document->saveXML($this->_node, LIBXML_NOEMPTYTAG);
                        $data = preg_replace('/<'.$this->name.'.*>/im', '', $data);
                        return preg_replace('/<\/'.$this->name.'.*>/im', '', $data);
                    }
                    case 'html': {
                        return $this->_document->saveHTML($this->_node);
                    }
                    case 'innerhtml': {
                        $data = $this->_document->saveHTML($this->_node);
                        $data = preg_replace('/<'.$this->name.'.*>/im', '', $data);
                        return preg_replace('/<\/'.$this->name.'.*>/im', '', $data);
                    }
                    case 'next': {
                        return $this->_node->nextSibling ? new XmlNode($this->_node->nextSibling, $this->_document) : null;
                    }
                    case 'prev': {
                        return $this->_node->previousSibling ? new XmlNode($this->_node->previousSibling, $this->_document) : null;
                    }
                    default: {
                        $item = $this->Item($property);
                        if(is_null($item)) {
                            $items = $this->getElementsByName($property);
                            if($items->Count() > 0){
                                $item = $items->First();
                            }
                            else {
                                if($this->type == 1){
                                    $item = $this->attributes->$property;
                                }
                            }
                        }
                        return $item;
                    }
                }
            }

            /**
             * Setter
             *
             * @param string $property
             * @param string @value
             * @return void
             */
            public function __set($property, $value) {
                switch(strtolower($property)) {
                    case 'value': {
                        $this->_node->nodeValue = $value;
                        break;
                    }
                    case 'cdata': {
                        $this->_node->appendChild($this->_document->createCDATASection($value));
                        break;
                    }
                    case 'raw': {
                        $this->_node = $value;
                        break;
                    }
                    case 'document': {
                        $this->_document = $value;
                        break;
                    }
                    default: {
                        break;
                    }
                }
            }

            /**
             * Возвращает обьект XmlNode соответстующий дочернему обьекту с именем $name
             *
             * @param string $name
             * @return XmlNode или null
             */
            public function Item($name) {
                $list = $this->Items($name);
                if($list->Count() > 0) {
                    return $list->First();
                }
                else {
                    return null;
                }
            }

            /**
             * Возвращает XmlNodeList с названием тэга $name
             *
             * @param string $name
             * @return XmlNodeList
             */
            public function Items($name) {
                return $this->Query('./child::'.$name);
            }

            /**
             * Проверяет является ли заданный узел дочерним к текущему
             *
             * @param XmlNode $node
             * @return boolean
             */
            public function IsChildOf($node) {
                $p = $this;
                while($p->parent) {
                    if($p->raw === $node->raw){
                        return true;
                    }
                    $p = $p->parent;
                }
                return false;
            }

            /**
             * Добавляет заданные узлы/узел в конец
             *
             * @param mixed $nodes
             * @return void
             */
            public function Append($nodes) {
                if($nodes instanceof XmlNode) {
                    if($nodes->name == 'html') {
                        $nodes = $nodes->body;
                        foreach($nodes->children as $node) {
                            $node->raw = $this->_document->importNode($node->raw, true);
                            $node->document = $this->_document;
                            $this->_node->appendChild($node->raw);
                        }
                    }
                    else {
                        $nodes->raw = $this->_document->importNode($nodes->raw, true);
                        $nodes->document = $this->_document;
                        $this->_node->appendChild($nodes->raw);

                    }
                }
                else if($nodes instanceof XmlNodeList) {
                    foreach($nodes as $node) {

                        if($node->name == 'html') {
                            $node = $node->body;
                            foreach($node->children as $n) {
                                $n->raw = $this->_document->importNode($n->raw, true);
                                $n->document = $this->_document;
                                $this->_node->appendChild($n->raw);
                            }
                        }
                        else {
                            $node->raw = $this->_document->importNode($node->raw, true);
                            $node->document = $this->_document;
                            $this->_node->appendChild($node->raw);
                        }
                    }
                }
            }

            /**
             * Добавляет заданные узлы/узел в перед узлом $relation
             *
             * @param mixed $nodes
             * @param XmlNode $relation
             * @return void
             */
            public function Insert($nodes, XmlNode $relation) {
                if($nodes instanceof XmlNode) {
                    $nodes->raw = $this->_document->importNode($nodes->raw, true);
                    $nodes->document = $this->_document;
                    $this->_node->insertBefore($nodes->raw, $relation->raw);
                }
                else if($nodes instanceof XmlNodeList) {
                    foreach($nodes as $node) {
                        $node->raw = $this->_document->importNode($node->raw, true);
                        $node->document = $this->_document;
                        $this->_node->insertBefore($node->raw, $relation->raw);
                    }
                }
            }

            /**
             * Удаляет текущий узел
             *
             * @return void
             */
            public function Remove() {
                $this->_node->parentNode->removeChild($this->_node);
            }

            /**
             * Заменяет текущий узел на заданный
             *
             * @param XmlNode $node
             * @return void
             */
            public function ReplaceTo(XmlNode $node) {
                $__node = $node->raw;
                $__node = $this->_document->importNode($__node, true);
                $this->_node->parentNode->replaceChild($__node, $this->_node);
                $this->_node = $__node;
            }

            /**
             * Возвращает элементы с атрибутом @name содержащим указанное имя
             *
             * @param string $name
             * @return XmlNamedNodeList
             */
            public function getElementsByName($name) {
                return $this->Query('./child::*[@name="'.$name.'"]', true);
            }

            /**
             * Выполняет XPath запрос
             *
             * @param string $query строка XPath
             * @param bool $returnAsNamedMap вернуть в виде именованого обьекта, в такон обьекте не может быть 2 тэгов с одним именем
             * @return XmlNodeList|XmlNamedNodeList
             */
            public function Query($query, $returnAsNamedMap = false) {
                $xq = new XmlQuery($this, $returnAsNamedMap);
                return $xq->Query($query);
            }

            
            public function ToObject($exclude = null) {

                if($exclude == null) {
                    $exclude = [];
                }

                if($this->attributes->Count() == 0 && $this->children->Count() == 0) {
                    if($this->isCData) {
                        return new XmlCData($this->value);
                    }
                    else {
                        return $this->value;
                    } 
                }

                $attributes = array();
                $content = array();

                foreach($this->attributes as $attr) {
                    $excluded = false;
                    if(is_array($exclude)) {
                        $excluded = in_array($attr->name, $exclude);
                    }
                    else if(is_callable($exclude)) {
                        $excluded = $exclude($this, $attr);
                    }
                    if (!$excluded) {
                        $attributes[$attr->name] = $attr->value;
                    }
                }

                if($this->children->Count() == 0)  {
                    if($this->isCData) {
                        $content = new XmlCData($this->value);
                    }
                    else {
                        $content = $this->value;
                    }   
                }
                else {
                    $content = [];
                    $children = $this->children;
                    foreach ($children as $child) {
                        
                        $excluded = false;
                        if(is_array($exclude)) {
                            $excluded = in_array($child->name, $exclude);
                        }
                        else if(is_callable($exclude)) {
                            $excluded = $exclude($this, $child);
                        }
                        
                        if ($excluded) {
                            continue;
                        }

                        $content[] = (object)[$child->name => $child->ToObject($exclude)];
                    }
                }

                if(is_array($content)) {
                    $retContent = [];
                    foreach($content as $item) {
                        $itemArray = get_object_vars($item);
                        $key = array_keys($itemArray)[0];
                        if(!isset($retContent[$key])) {
                            $retContent[$key] = [];
                        }
                        $retContent[$key][] = $item->$key;
                    }

                    foreach($retContent as $key => $value) {
                        if(count($value) == 1) {
                            $retContent[$key] = reset($value);
                        }
                    }

                    $content = $retContent;
        
                }

                if(!count($attributes) && is_array($content) && !count($content)) {
                    return null;
                }

                return new XmlSerialized($this->name, $attributes, $content);
            }

            public static function FromObject(XmlSerialized $xmlSerializedObject, $elementDefinition = null) {
                
                $xml = XmlNode::LoadNode('<'.$xmlSerializedObject->name.' />', 'utf-8');
                foreach($xmlSerializedObject->attributes as $name => $value) {

                    $attributeDefinition = $elementDefinition && isset($elementDefinition->attributes[$name]) ? $elementDefinition->attributes[$name] : null;
                    
                    if($attributeDefinition) {
                        $baseType = $attributeDefinition->type->restrictions->base;
                        // есть специфика только, если это булево значение
                        if($baseType == 'boolean') {
                            $value = $value ? 'true' : 'false';
                        }
                    }

                    $xml->attributes->Append($name, $value);
                }

                if ($xmlSerializedObject->content) {
                    if($xmlSerializedObject->content instanceof XmlCData) {
                        $xml->cdata = $xmlSerializedObject->content->value;
                    }
                    else {
                        foreach ($xmlSerializedObject->content as $name => $element) {
                            if (!is_array($element)) {
                                $element = [$element];
                            }
                            foreach ($element as $el) {
                                if (is_string($el)) {
                                    $xml->Append(XmlNode::LoadNode('<'.$name.'>'.$el.'</'.$name.'>'));
                                } elseif ($el instanceof XmlCData) {
                                    $xml->Append(XmlNode::LoadNode('<'.$name.'><![CDATA['.$el->value.']]></'.$name.'>'));
                                } elseif ($el instanceof XmlSerialized && isset($elementDefinition->elements[$name])) {
                                    $xml->Append(XmlNode::FromObject($el, $elementDefinition->elements[$name]));
                                }
                            }
                        }
                    }
                }

                
                return $xml;


            }

        }

    }
