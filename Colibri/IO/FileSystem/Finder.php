<?php
    /**
     * FileSystem
     * 
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\FileSystem
     */
    namespace Colibri\IO\FileSystem {

        use Colibri\Collections\ArrayList;
        use Colibri\Helpers\Variable;

        /**
         * Класс помогающий искать файлы и директории
         */
        class Finder {

            public function __construct() { }

            /**
             * Найти файлы
             *
             * @param string $path
             * @param string $match
             * @param boolean $sortField
             * @param boolean $sortType
             * @return ArrayList
             */
            public function Files($path, $match = '', $sortField = false, $sortType = false) {
                if(!Directory::Exists($path)) {
                    return new ArrayList();
                }

                $ret = new ArrayList();

                if ($handle = opendir($path)) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && filetype($path . '/' . $file) != "dir") {

                            if(!Variable::IsEmpty($match) && preg_match($match, $file) == 0) {
                                continue;
                            }
                            $ret->Add(new File($path . '/' . $file));

                        }
                    }
                    closedir($handle);
                }

                if($sortField) {
                    $ret->Sort($sortField, $sortType);
                }
                return $ret;
            }

            /**
             * Найти директории
             *
             * @param string $path
             * @param boolean $sortField
             * @param boolean $sortType
             * @return ArrayList
             */
            public function Directories($path, $sortField = false, $sortType = false) {
                if(!Directory::Exists($path)) {
                    return new ArrayList();
                }

                $ret = new ArrayList();
                if ($handle = opendir($path)) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && @filetype($path . '/' . $file) == "dir") {
                            $ret->Add(new Directory($path . $file . '/'));
                        }
                    }
                    closedir($handle);
                }
                if($sortField) {
                    $ret->Sort($sortField, $sortType);
                }
                return $ret;
            }

            /**
             * Вернуть папки в директории
             *
             * @param string $path
             * @return ArrayList
             */
            public function Children($path) {
                if(!Directory::Exists($path)) {
                    return new ArrayList();
                }

                $ret = new ArrayList();

                if ($handle = opendir($path)) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != "..") {
                            if(@filetype($path . '/' . $file) == "dir") {
                                $ret->Add(new Directory($path . '/' . $file));
                            }
                            else {
                                $ret->Add(new File($path . '/' . $file));
                            }
                        }
                    }
                    closedir($handle);
                }
                return $ret;
            }

        }

    }