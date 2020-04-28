<?php

    namespace Colibri\Threading {

        class ErrorCodes
        {
            const UnknownProperty = 1;
    
            public static function ToString($code)
            {
                if ($code == ErrorCodes::UnknownProperty) {
                    return 'Unknown property';
                }
                return null;
            }
        }

    }
