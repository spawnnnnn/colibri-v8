<?php
    /**
     * Mail
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Mail
     */
    namespace Colibri\IO\Mail {

        use Colibri\App;
        use Colibri\Helpers\Date;
        use Colibri\Helpers\Variable;

        /**
         * Отправитель
         */
        class Sender {

            const Version           = '5.1';
            
            public $DOMAIN          = '';

            public $Mailer          = MailerTypes::Mail;
            
            public $Sendmail        = '/usr/sbin/sendmail';
            
            public $Sender          = '';
            
            public $Host            = 'localhost';
            public $Port            = 25;
            public $Helo            = '';
            public $Username        = '';
            public $UserPass        = '';
            public $Timeout         = 10;
            
            public $SMTPSecure      = '';
            public $SMTPAuth        = false;
            public $SMTPDebug       = false;
            public $SMTPKeepAlive   = false;
            
            public $DKIM            = null;
            public $certificate     = null;
            

            /**
            * Callback Action function name
            * the function that handles the result of the send email action. Parameters:
            *   bool    $result        result of the send action
            *   string  $to            email address of the recipient
            *   string  $cc            cc email addresses
            *   string  $bcc           bcc email addresses
            *   string  $subject       the subject
            *   string  $body          the email body
            * @var string
            */
            public $callback        = ''; 

            /////////////////////////////////////////////////
            // PROPERTIES, PRIVATE AND PROTECTED
            /////////////////////////////////////////////////

            private   $smtp         = NULL;
            private   $boundary     = array();
            
            /////////////////////////////////////////////////
            // METHODS, VARIABLES
            /////////////////////////////////////////////////

            /**
            * Constructor
            * @param boolean $exceptions Should we throw external exceptions?
            */
            public function __construct($mailer) {
                $this->Mailer = $mailer;
            }
            
            public static function Create($mailer, $domain = '') {

                $config = App::$config->Query('mailer.'.$mailer)->AsObject();
                
                $sender = new Sender($mailer);
                if($mailer == MailerTypes::Smtp) {
                    
                    $sender->Host = $config->host;
                    $sender->Port = $config->port;
                    $sender->SMTPAuth = $config->auth != 'false';
                    if($config->secure == 'false') {
                        $sender->SMTPSecure = false;
                    }
                    else {
                        $sender->SMTPSecure = $config->secure;
                    }

                    $sender->SMTPOptions = array (
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                    $sender->Timeout = $config->timeout;
                    if($sender->SMTPAuth) {
                        $sender->Username = $config->usr;
                        $sender->UserPass = $config->pwd;
                    }
                }
                else if ($mailer == MailerTypes::Mail) {
                    $sender->Username = $config->from;
                    $sender->Sender = $config->from;
                    $sender->IsMail();
                }
                else if ($mailer == MailerTypes::SendMail) {
                    $sender->Username = $config->from;
                    $sender->Sender = $config->from;
                    $sender->IsSendmail();
                }
                
                $sender->DOMAIN = $domain;
                
                return $sender;
            }

            /**
            * Sets Mailer to send message using SMTP.
            * @return void
            */
            public function IsSMTP() {
                $this->Mailer = 'smtp';
            }

            /**
            * Sets Mailer to send message using PHP mail() function.
            * @return void
            */
            public function IsMail() {
                $this->Mailer = 'mail';
            }

            /**
            * Sets Mailer to send message using the $Sendmail program.
            * @return void
            */
            public function IsSendmail() {
                if (!stristr(ini_get('sendmail_path'), 'sendmail')) {
                    $this->Sendmail = '/var/qmail/bin/sendmail';
                }
                $this->Mailer = 'sendmail';
            }

            /**
            * Sets Mailer to send message using the qmail MTA.
            * @return void
            */
            public function IsQmail() {
                if (stristr(ini_get('sendmail_path'), 'qmail')) {
                    $this->Sendmail = '/var/qmail/bin/sendmail';
                }
                $this->Mailer = 'sendmail';
            }

            /**
            * Creates message and assigns Mailer. If the message is
            * not sent successfully then it returns false.  Use the ErrorInfo
            * variable to view description of the error.
            * @return bool
            */
            public function Send(Message $m) {
                
                if($m->to->count + $m->cc->count + $m->bcc->count < 1){
                    throw new Exception(ErrorMessages::ProvideAddress);
                }
                
                $header = $this->_createHeader($m);
                $body = $this->_createBody($m);

                
                switch($this->Mailer) {
                    case 'sendmail':
                    // не тестировалось
                        return $this->SendmailSend($m, $header, $body);
                    case 'smtp':
                        return $this->SmtpSend($m, $header, $body);
                    default:
                    // не тестировалось
                        return $this->MailSend($m, $header, $body);
                }

            }

            private function SendmailSend(Message $m, $header, $body) {
                
                if ($this->Sender != ''){
                    $sendmail = sprintf("%s -oi -f %s -t", escapeshellcmd($this->Sendmail), escapeshellarg($this->Sender));
                }
                else{
                    $sendmail = sprintf("%s -oi -t", escapeshellcmd($this->Sendmail));
                }
                    
                // исправить - из Address-а только адрес используется 
                foreach ($m->to as $val) {
                    
                    if(!@$mail = popen($sendmail, 'w')){
                        throw new Exception(ErrorMessages::Execute.$this->Sendmail);
                    }
                    
                    fputs($mail, "To: " . $val->address . "\n");
                    fputs($mail, $header);
                    fputs($mail, $body);
                    $result = pclose($mail);
                    
                    // implement call back function if it exists
                    $this->doCallback(($result == 0) ? 1 : 0, $val, $m->cc, $m->bcc, $m->subject, $body);
                    
                    if($result != 0){
                        throw new Exception(ErrorMessages::Execute.$this->Sendmail);
                    }
                }
                
            
                return true;
            }

            private function MailSend(Message $m, $header, $body) {
                $toArr = array();
                
                foreach($m->to as $t) {
                    $toArr[] = $t->formated;
                }
                
                $to = implode(', ', $toArr);

                $params = sprintf("-oi -f %s", $this->Sender);
                    
                $old_from = ini_get('sendmail_from');
                @ini_set('sendmail_from', $this->Sender);
                
                $rt = @mail($to, Helper::EncodeHeader(Helper::StripNewLines($m->subject), 'text', $m->charset), $body, $header, $params);

                // implement call back function if it exists
                $this->doCallback(($rt == 1) ? 1 : 0, $to, $m->cc, $m->bcc, $m->subject, $body);
                
                @ini_set('sendmail_from', $old_from);
                
                if(!$rt){
                    throw new Exception(ErrorMessages::Instantiate);
                }
                
                return true;
            }

            private function SmtpSend(Message $m, $header, $body) {
                
                $bad_rcpt = array();
                
                if(!$this->SmtpConnect()){
                    throw new Exception(ErrorMessages::SmtpConnectFailed);
                }
                
                
                $smtp_from = ($this->Sender == '') ? $m->from->address : $this->Sender;
                if(!$this->smtp->Mail($smtp_from)){
                    throw new Exception(ErrorMessages::FromFailed . $smtp_from);
                }

                // Attempt to send attach all recipients
                foreach($m->to as $to) {
                    if (!$this->smtp->Recipient($to->address)) {
                        $bad_rcpt[] = $to->address;
                        // implement call back function if it exists
                        $this->doCallback(0, $to->address, '', '', $m->subject, $body);
                    } else {
                        // implement call back function if it exists
                        $this->doCallback(1, $to->address, '', '', $m->subject, $body);   
                    }
                }
                
                foreach($m->cc as $cc) {
                    if (!$this->smtp->Recipient($cc->address)) {
                        $bad_rcpt[] = $cc->formated;
                        // implement call back function if it exists
                        $this->doCallback(0, '', $cc->address, '', $m->subject, $body);
                    } else {
                        // implement call back function if it exists
                        $this->doCallback(1, '', $cc->address, '', $m->subject, $body);
                    }
                }
                
                foreach($m->bcc as $bcc) {
                    if (!$this->smtp->Recipient($bcc->address)) {
                        $bad_rcpt[] = $bcc->formated;
                        // implement call back function if it exists
                        $this->doCallback(0, '', '', $bcc->address, $m->subject, $body);
                    } else {
                        // implement call back function if it exists
                        $this->doCallback(1, '', '', $bcc->address, $m->subject, $body);
                    }
                }


                if (count($bad_rcpt) > 0 ){
                    throw new Exception(ErrorMessages::RecipientsFailed . implode(', ', $bad_rcpt));
                }
                
                if(!$this->smtp->Data($header . $body)){
                    throw new Exception(ErrorMessages::DataNotAccepted);
                }

                if($this->SMTPKeepAlive) {
                    $this->smtp->Reset();
                }

                return true;
            }

            private function SmtpConnect() {
                
                if(Variable::IsNull($this->smtp)){
                    $this->smtp = new SMTP();
                }

                $this->smtp->do_debug = $this->SMTPDebug;
                $hosts = explode(';', $this->Host);
                $index = 0;
                $connection = $this->smtp->Connected();

                // Retry while there is no connection
                try {
                    
                    while($index < count($hosts) && !$connection) {
                        
                        $hostinfo = array();
                        
                        if (preg_match('/^(.+):([0-9]+)$/', $hosts[$index], $hostinfo)) {
                            $host = $hostinfo[1];
                            $port = $hostinfo[2];
                        } else {
                            $host = $hosts[$index];
                            $port = $this->Port;
                        }

                        $tls = ($this->SMTPSecure == 'tls');
                        $ssl = ($this->SMTPSecure == 'ssl');

                        if ($this->smtp->Connect(($ssl ? 'ssl://':'').$host, $port, $this->Timeout)) {

                            $hello = ($this->Helo != '' ? $this->Helo : $this->_hostName());
                            $this->smtp->Hello($hello);

                            if ($tls) {
                                if (!$this->smtp->StartTLS()){
                                    throw new Exception(ErrorMessages::TLS);
                                }

                                //We must resend HELO after tls negotiation
                                $this->smtp->Hello($hello);
                            }

                            $connection = true;
                            if ($this->SMTPAuth && !$this->smtp->Authenticate($this->Username, $this->UserPass)){
                                throw new Exception(ErrorMessages::Authenticate);
                            }
                        }
                        
                        $index++;
                        if (!$connection){
                            throw new Exception(ErrorMessages::ConnectHost);
                        }
                            
                    }
                    
                } 
                catch (Exception $e) {
                    
                    $this->smtp->Reset();
                    throw $e;
                    
                }
                
                return true;
                
            }

            private function SmtpClose() {
                if(!is_null($this->smtp) && $this->smtp->Connected()) {
                    $this->smtp->Quit();
                    $this->smtp->Close();
                }
            }

            /**
            * Creates recipient headers.
            * @access public
            * @return string
            */
            private function _appendToHeader($type, $addr) {
                if($addr instanceOf Address){
                    return Helper::HeaderLine($type, $addr->formated);
                }
                elseif($addr instanceOf AddressList){
                    return Helper::HeaderLine($type, $addr->Join());
                }
                else{
                    return Helper::HeaderLine($type, '');
                }
            }

            

            /**
            * Set the body wrapping.
            * @access public
            * @return void
            */
            private function _setWordWrap($m) {
                if($m->wordwrap < 1){
                    return;
                }

                switch($m->type) {
                    case 'alt':
                    case 'alt_attachments':
                        $m->altbody = Helper::WrapText($m->altbody, $m->wordwrap, $m->charset);
                        break;
                    default:
                        $m->body = Helper::WrapText($m->body, $m->wordwrap, $m->charset);
                        break;
                }
            }

            /**
            * Assembles message header.
            * @access public
            * @return string The assembled header
            */
            private function _createHeader(Message $m) {
                $result = '';
                
                // Set the boundaries
                $uniq_id = md5(uniqid(time()));
                $this->boundary[1] = 'b1_' . $uniq_id;
                $this->boundary[2] = 'b2_' . $uniq_id;
                
                $result .= Helper::HeaderLine('Date', Date::RFC());
                if($m->returnpath != '') {
                    $result .= Helper::HeaderLine('Return-Path', '<' . trim($m->returnpath) . '>');
                }
                else {
                    if(Variable::IsNull($this->Sender)) {
                        $result .= Helper::HeaderLine('Return-Path', trim($m->from->formated));
                    }
                    else if(Variable::IsObject($this->Sender)) {
                        $result .= Helper::HeaderLine('Return-Path', trim($this->Sender->formated));
                    }
                    else if(Variable::IsString($this->Sender)) {
                        $result .= Helper::HeaderLine('Return-Path', trim($this->Sender));
                    }
                }
                $result .= $this->_appendToHeader('From', $m->from);

                // To be created automatically by mail()
                if($this->Mailer != MailerTypes::Mail) {
                    if($m->to->count > 0) {
                        $result .= $this->_appendToHeader('To', $m->to);
                    } elseif($this->cc->count == 0) {
                        $result .= $this->_appendToHeader('To', 'undisclosed-recipients:;');
                    }
                }
                
                // sendmail and mail() extract Cc from the header before sending
                if($m->cc->count > 0){
                    $result .= $this->_appendToHeader('Cc', $m->cc);
                }

                // sendmail and mail() extract Bcc from the header before sending
                if((($this->Mailer == MailerTypes::SendMail) || 
                    ($this->Mailer == MailerTypes::Mail)) && 
                    ($m->bcc->count > 0)){
                    $result .= $this->_appendToHeader('Bcc', $m->bcc);
                }

                if($m->replyto->count > 0){
                    $result .= $this->_appendToHeader('Reply-to', $m->replyto);
                }
                // mail() sets the subject itself
                if($this->Mailer != MailerTypes::Mail){
                $result .= Helper::HeaderLine('Subject', Helper::EncodeHeader(Helper::StripNewLines($m->subject), 'text', $m->charset));
                }

                if($m->id != ''){
                    $result .= Helper::HeaderLine('Message-ID', '<'.$m->id.'@'.$this->_hostName().'>');
                }
                else{
                    $result .= sprintf("Message-ID: <%s@%s>%s", $uniq_id, $this->_hostName(), Helper::LE);
                }

                if($m->confirmreadingto != '') {
                    $result .= Helper::HeaderLine('Disposition-Notification-To', '<' . trim($m->confirmreadingto) . '>');
                    $result .= Helper::HeaderLine('X-Confirm-Reading-To', '<' . trim($m->confirmreadingto) . '>');
                    $result .= Helper::HeaderLine('Return-Receipt-To', '<' . trim($m->confirmreadingto) . '>');
                }

                // Add custom headers
                for($index = 0; $index < count($m->customheader); $index++){
                    $result .= Helper::HeaderLine(trim($m->customheader[$index][0]), Helper::EncodeHeader(trim($m->customheader[$index][1]), 'text', $m->charset));
                }
                    
                if (!$this->certificate) {
                    $result .= Helper::HeaderLine('MIME-Version', '1.0');
                    $result .= $this->_getMailMIME($m);
                }

                return $result;
            }

            /**
            * Returns the message MIME.
            * @access public
            * @return string
            */
            private function _getMailMIME($m) {
                
                $result = '';
                switch($m->type) {
                    case 'plain':
                        $result .= Helper::HeaderLine('Content-Transfer-Encoding', $m->encoding);
                        $result .= sprintf("Content-Type: %s; charset=\"%s\"", $m->contenttype, $m->charset);
                        break;
                    case 'attachments':
                    case 'alt_attachments':
                        if($m->attachments->HasInline()){
                            $result .= sprintf("Content-Type: %s;%s\ttype=\"text/html\";%s\tboundary=\"%s\"%s", 'multipart/related', Helper::LE, Helper::LE, $this->boundary[1], Helper::LE);
                        }
                        else {
                            $result .= Helper::HeaderLine('Content-Type', 'multipart/mixed;');
                            $result .= Helper::TextLine("\tboundary=\"" . $this->boundary[1] . '"');
                        }
                        break;
                    case 'alt':
                        $result .= Helper::HeaderLine('Content-Type', 'multipart/alternative;');
                        $result .= Helper::TextLine("\tboundary=\"" . $this->boundary[1] . '"');
                        break;
                    default: {
                        break;
                    }
                }

                if($this->Mailer != MailerTypes::Mail){
                    $result .= Helper::LE.Helper::LE;
                }
                return $result;
            }

            /**
            * Assembles the message body.  Returns an empty string on failure.
            * @access public
            * @return string The assembled message body
            */
            private function _createBody(Message $m) {
                $body = '';

                if ($this->certificate){
                    $body .= $this->_getMailMIME($m);
                }

                $this->_setWordWrap($m);

                try {
                    switch($m->type) {
                        case 'alt':{
                            $body .= Helper::GetBoundaryBegin($this->boundary[1], '', 'text/plain', '');
                            $body .= Helper::EncodeString($m->altbody, $m->encoding);
                            $body .= Helper::LE.Helper::LE;
                            $body .= Helper::GetBoundaryBegin($this->boundary[1], '', 'text/html', '');
                            $body .= Helper::EncodeString($m->Body, $m->encoding);
                            $body .= Helper::LE.Helper::LE;
                            $body .= Helper::GetBoundaryEnd($this->boundary[1]);
                            break;
                        }
                        case 'plain':{
                            $body .= Helper::EncodeString($m->body, $m->encoding);
                            break;
                        }
                        case 'attachments':{
                            $body .= Helper::GetBoundaryBegin($this->boundary[1], $m->charset, 'text/html', $m->encoding);
                            $body .= Helper::EncodeString($m->Body, $m->encoding);
                            $body .= Helper::LE;
                            $body .= $this->AttachAll($m);
                            break;
                        }
                        case 'alt_attachments':{
                            $body .= sprintf("--%s%s", $this->boundary[1], Helper::LE);
                            $body .= sprintf("Content-Type: %s;%s" . "\tboundary=\"%s\"%s", 'multipart/alternative', Helper::LE, $this->boundary[2], Helper::LE.Helper::LE);
                            $body .= Helper::GetBoundaryBegin($this->boundary[2], '', 'text/plain', '') . Helper::LE; // Create text body
                            $body .= Helper::EncodeString($m->altbody, $m->encoding);
                            $body .= Helper::LE.Helper::LE;
                            $body .= Helper::GetBoundaryBegin($this->boundary[2], '', 'text/html', '') . Helper::LE; // Create the HTML body
                            $body .= Helper::EncodeString($m->body, $m->encoding);
                            $body .= Helper::LE.Helper::LE;
                            $body .= Helper::GetBoundaryEnd($this->boundary[2]);
                            $body .= $this->AttachAll($m);
                            break;
                        }
                        default: {
                            break;
                        }
                    }
                }
                catch(Exception $e) {
                    $body = '';
                }
                
                if($body != '' && $this->certificate) {
                    try {
                        $file = tempnam('', 'mail');
                        file_put_contents($file, $body);
                        $signed = tempnam("", "signed");
                        if (@openssl_pkcs7_sign($file, $signed, "file://".$this->certificate->file, array("file://".$this->certificate->key, $this->certificate->pass), array())) {
                            @unlink($file);
                            @unlink($signed);
                            $body = file_get_contents($signed);
                        } else {
                            @unlink($file);
                            @unlink($signed);
                            throw new Exception(ErrorMessages::Signing.openssl_error_string());
                        }
                    } catch (Exception $e) {
                        $body = '';
                        throw $e;
                    }
                }

                return $body;
            }

            /**
            * Attaches all fs, string, and binary attachments to the message.
            * Returns an empty string on failure.
            * @access private
            * @return string
            */
            private function AttachAll(Message $m) {
                // Return text of body
                $mime = array();
                $cidUniq = array();
                $incl = array();

                // Add all attachments
                foreach ($m->attachments as $attachment) {
                                    
                    // Check for string attachment
                    if ($attachment->isString){
                        $string = $attachment->path;
                    }
                    else{
                        $path = $attachment->path;
                    }
                    

                    if (in_array($attachment->path, $incl)) {
                        continue; 
                    }
                    
                    $name        = $attachment->name;
                    $encoding    = $attachment->encoding;
                    $type        = $attachment->type;
                    $disposition = $attachment->isInline ? 'inline' : 'attachment';
                    $cid         = $attachment->cid;
                    $incl[]      = $attachment->path;
                    
                    if ( $disposition == 'inline' && isset($cidUniq[$cid]) ) {
                        continue;
                    }
                        
                    $cidUniq[$cid] = true;

                    $mime[] = sprintf("--%s%s", $this->boundary[1], Helper::LE);
                    $mime[] = sprintf("Content-Type: %s; name=\"%s\"%s%s", $type, Helper::EncodeHeader(Helper::StripNewLines($name), 'text', $m->charset), ($attachment->charset ? '; charset='.$attachment->charset : '') ,Helper::LE);
                    $mime[] = sprintf("Content-Transfer-Encoding: %s%s", $encoding, Helper::LE);
                    
                    if($disposition == 'inline'){
                        $mime[] = sprintf("Content-ID: <%s>%s", $cid, Helper::LE);
                    }

                    $mime[] = sprintf("Content-Disposition: %s; filename=\"%s\"%s", $disposition, Helper::EncodeHeader(Helper::StripNewLines($name), 'text', $m->charset), Helper::LE.Helper::LE);

                    // Encode as string attachment
                    if($attachment->isString) {
                        $mime[] = Helper::EncodeString($string, $encoding);
                        $mime[] = Helper::LE.Helper::LE;
                    } else {
                        $mime[] = Helper::EncodeFile($path, $encoding);
                        $mime[] = Helper::LE.Helper::LE;
                    }
                }

                $mime[] = sprintf("--%s--%s", $this->boundary[1], Helper::LE);
                
                return join('', $mime);
            }

            /**
            * Returns the server hostname or 'localhost.localdomain' if unknown.
            * @access private
            * @return string
            */
            private function _hostName() {
                
                if($this->DOMAIN != ''){
                    $result = $this->DOMAIN;
                }
                else {
                    if (isset($_SERVER['SERVER_NAME'])){
                        $result = $_SERVER['SERVER_NAME'];
                    }
                    else{
                        $result = 'localhost.localdomain';
                    }
                }
                return $result;
            }

            
            protected function doCallback($isSent,$to,$cc,$bcc,$subject,$body) {
                if (!empty($this->callback) && function_exists($this->callbackcallback)) {
                $params = array($isSent,$to,$cc,$bcc,$subject,$body);
                call_user_func_array($this->callback,$params);
                }
            }
        }

    }