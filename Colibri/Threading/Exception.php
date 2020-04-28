<?php

    namespace Colibri\Threading {

        use Colibri\AppException;

        class Exception extends AppException
        {
            public function __construct($code, $message)
            {
                return parent::__construct(ErrorCodes::ToString($code).' '.$message, $code);
            }
        }

    }
