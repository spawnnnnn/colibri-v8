<?php

    /**
     * Helpers
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\Helpers
     */
    namespace Colibri\Helpers {

        /**
         * Класс обертка над датой
         */
        class Date
        {

            /** Количество секунд в году */
            const YEAR  = 31556926;
            /** Количество секунд в месяце */
            const MONTH = 2629744;
            /** Количество секунд в неделю */
            const WEEK  = 604800;
            /** Количество секунд в дне */
            const DAY   = 86400;
            /** Количество секунд в час */
            const HOUR  = 3600;
            /** Количество секунд в минуту */
            const MINUTE= 60;

            /**
             * Вывести в формате RFC
             *
             * @param integer $time
             * @return string
             */
            public static function RFC($time = null)
            {
                $tz = date('Z');
                $tzs = ($tz < 0) ? '-' : '+';
                $tz = abs($tz);
                $tz = (int)($tz/3600)*100 + ($tz%3600)/60;
                return sprintf("%s %s%04d", date('D, j M Y H:i:s', Variable::IsNull($time) ? time() : $time), $tzs, $tz);
            }

            /**
             * Вернуть в формате для базы данных
             *
             * @param int $time
             * @param boolean $format
             * @return string
             */
            public static function ToDbString($time = null, $format = '%Y-%m-%d %H:%M:%S')
            {
                if (Variable::IsNull($time)) {
                    $time = time();
                } else {
                    $time = (Variable::IsNumeric($time) ? $time : strtotime($time));
                }
                return TimeZone::FTimeU($format, $time);
            }

            /**
             * Вернуть дату в читабельном виде
             *
             * @param int $time
             * @param boolean $showTime
             * @return string
             */
            public static function ToHumanDate($time = null, $showTime = false)
            {
                if (is_null($time)) {
                    $time = time();
                }
                return ((int)strftime('%d', $time)).' '.TimeZone::Month2(strftime('%m', $time) - 1).' '.strftime('%Y', $time).($showTime ? ' '.strftime('%H', $time).':'.strftime('%M', $time) : '');
            }

            /**
             * Строка в цифру
             *
             * @param string $datestring
             * @return integer
             */
            public static function ToUnixTime($datestring)
            {
                return strtotime($datestring);
            }

            /**
             * Количество лет между датами
             *
             * @param integer $time
             * @return integer
             */
            public static function Age($time)
            {
                $time = time() - $time; // to get the time since that moment

                $tokens = array(
                    31536000 => array('год', 'года', 'лет'),
                    2592000 => array('месяц', 'месяца', 'месяцев'),
                    604800 => array('неделю', 'недели', 'недель'),
                    86400 => array('день', 'дня', 'дней'),
                    3600 => array('час', 'часа', 'часов'),
                    60 => array('минуту', 'минуты', 'минут'),
                    1 => array('секунду', 'секунды', 'секунд')
                );

                foreach ($tokens as $unit => $labels) {
                    if ($time < $unit) {
                        continue;
                    }
                    $numberOfUnits = floor($time / $unit);
                    $ret = ($numberOfUnits > 1 ? $numberOfUnits.' ' : '').Strings::FormatSequence($numberOfUnits, $labels).' назад';
                    if ($ret == 'день назад') {
                        $ret = 'вчера';
                    }
                    return $ret;
                }

                return 'только что';
            }

            /**
             * Количество лет между датами (зачем это ? не знаю)
             *
             * @param integer $time
             * @return integer
             */
            public static function AgeYears($time)
            {
                $day = date('j', $time);
                $month = date('n', $time);
                $year = date('Y', $time);

                $age = date('Y') - $year;
                $m = date('n') - $month;
                if ($m < 0 || ($m === 0 && date('j') < $day)) {
                    $age--;
                }

                return $age;
            }

            /**
             * Количество секунд в время HH:MM:SS
             *
             * @param integer $number
             */
            public static function TimeToString($number)
            {
                $hours = 0;
                $mins = 0;
                $secs = 0;

                if ($number >= 60) {
                    $secs = $number % 60;
                    $number = (int)($number / 60);
                    if ($number >= 60) {
                        $mins = $number % 60;
                        $number = (int)($number / 60);
                        if ($number >= 60) {
                            $hours = $number % 60;
                            $number = (int)($number / 60);
                        } else {
                            $hours = $number;
                        }
                    } else {
                        $mins = $number;
                    }
                } else {
                    $secs = $number;
                }

                $txt = "";
                $txt .= Strings::Expand($hours, 2, "0").":";
                $txt .= Strings::Expand($mins, 2, "0").":";
                $txt .= Strings::Expand($secs, 2, "0").":";

                $txt = ltrim($txt, "0");
                $txt = ltrim($txt, ":");

                return substr($txt, 0, strlen($txt)-1);
            }
        }

    }
