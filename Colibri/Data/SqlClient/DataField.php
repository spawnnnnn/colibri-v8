<?php

    namespace Colibri\Data\SqlClient {

        class DataField {

            /**
             * Имя столбца
             *
             * @var string
             */
            public $name;

            /**
             * Исходное имя столбца, если у него есть псевдоним
             * 
             * @var string
             */
            public $originalName;
            
            /**
             * Имя таблицы, которой принадлежит столбец (если не вычислено)
             */
            public $table;	

            /**
             * Исходное имя таблицы, если есть псевдоним
             *
             * @var string
             */
            public $originalTable;

            /**
             * Имя таблицы, имя поля в формате необходимом для работы с базой данных
             * 
             * @var string
             */
            public $escaped;

            /**
             * Зарезервировано для значения по умолчанию, на данный момент всегда ""
             *
             * @var string
             */	
            public $def;

            /**
             * Максимальная ширина поля результирующего набора.
             *
             * @var int
             */
            public $maxLength;
            
            /**
             * Ширина поля, как она задана при определении таблицы.
             *
             * @var int
             */
            public $length;

            /**
             * Целое число, представляющее битовые флаги для поля.
             *
             * @var int
             */
            public $flags;

            /**
             * Тип данных поля
             *
             * @var string
             */
            public $type;	

            /**
             * Число знаков после запятой (для числовых полей)
             *
             * @var int
             */
            public $decimals;

        }

    }