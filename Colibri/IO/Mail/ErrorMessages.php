<?php
    /**
     * Mail
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Mail
     */
    namespace Colibri\IO\Mail {

        /**
         * Сообщения об ошибках
         */
        class ErrorMessages {
            const ProvideAddress = 'You must provide at least one recipient email address.';
            const MailerNotSupported = ' mailer is not supported.';
            const Execute = 'Could not execute: ';
            const Instantiate = 'Could not instantiate mail function.';
            const Authenticate = 'SMTP Error: Could not authenticate.';
            const FromFailed = 'The following From address failed: ';
            const EecipientsFailed = 'SMTP Error: The following recipients failed: ';
            const DataNotAccepted = 'SMTP Error: Data not accepted.';
            const ConnectHost = 'SMTP Error: Could not connect to SMTP host.';
            const FileAccess = 'Could not access file: ';
            const FileOpen = 'File Error: Could not open file: ';
            const Encoding = 'Unknown encoding: ';
            const Signing = 'Signing Error: ';
            const SmtpError = 'SMTP server error: ';
            const SmtpConnectFailed = 'SMTP connection failed: ';
            const EmptyMessage = 'Message body empty';
            const InvalidAddress = 'Invalid address';
            const VariableSet = 'Cannot set or reset variable: ';
            const InvalidArgument = 'Invalid argument: ';
            const RecipientsFailed = 'Recipients failed: ';
            const TLS = "Error connect with TLS";
            const SendError = "Error sending mail";
        }

    }