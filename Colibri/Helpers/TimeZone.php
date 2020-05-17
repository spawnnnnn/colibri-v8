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
         * Временная зона
         */
        class TimeZone
        {

            /**
             * Зона по умолчанию
             */
            public static $zone = 'ru';

            /**
             * Строки
             */
            public static $texts = array(
                'ru' => array(
                    'months' => array('январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'),
                    'months2' => array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'),
                    'weekdays' => array('понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота', 'воскресенье'),
                )
            );

            /**
             * Установить зону глобально
             *
             * @param string $zone
             * @return void
             */
            public static function Set($zone)
            {
                self::$zone = $zone;
            }

            /**
             * Возвращает название месяца в текущей зоне (локализации)
             *
             * @param integer $month
             * @return string
             */
            public static function Month($month)
            {
                return self::$texts[self::$zone]['months'][$month];
            }

            /**
             * Возвращает название месяца в текущей локализации в родительном падеже
             *
             * @param integer $month
             * @return string
             */
            public static function Month2($month)
            {
                return self::$texts[self::$zone]['months2'][$month];
            }

            /**
             * Возвращает название недели в текущей локализации
             *
             * @param integer $weekday
             * @return string
             */
            public static function Weekday($weekday)
            {
                return self::$texts[self::$zone]['weekdays'][$weekday];
            }

            /**
             * Форматирует строку с учетом зоны
             *
             * @param string $format
             * @param float $microtime
             * @return string
             */
            public static function FTimeU($format, $microtime)
            {
                if (preg_match('/^[0-9]*\\.([0-9]+)$/', $microtime, $reg)) {
                    $decimal = substr(str_pad($reg[1], 6, "0"), 0, 6);
                } else {
                    $decimal = "000000";
                }
                $format = preg_replace('/(%f)/', $decimal, $format);
                return strftime($format, $microtime);
            }
        }

    }
