<?php
    /**
     * Mail
     *
     * @author Vahan P. Grigoryan <vahan.grigoryan@gmail.com>
     * @copyright 2019 Colibri
     * @package Colibri\IO\Mail
     */
    namespace Colibri\IO\Mail {

        class Helper
        {
            const LE = "\n";
            const LF = "\n";
            const CR = "\r";
            const CRLF = "\r\n";

            public static function FixEOL($line, $char = self::LE)
            {
                $line = str_replace(self::CRLF, $char, $line);
                $line = str_replace(self::CR, $char, $line);
                return str_replace(self::LF, $char, $line);
            }
    
            /**
            * Finds last character boundary prior to maxLength in a utf-8
            * quoted (printable) encoded string.
            * Original written by Colin Brown.
            * @access public
            * @param string $encodedText utf-8 QP text
            * @param int    $maxLength   find last character boundary prior to this length
            * @return int
            */
            public static function UTF8CharBoundary($encodedText, $maxLength)
            {
                $foundSplitPos = false;
                $lookBack = 3;
                while (!$foundSplitPos) {
                    $lastChunk = substr($encodedText, $maxLength - $lookBack, $lookBack);
                    $encodedCharPos = strpos($lastChunk, "=");
                    if ($encodedCharPos !== false) {
                        // Found start of encoded character byte within $lookBack block.
                        // Check the encoded byte value (the 2 chars after the '=')
                        $hex = substr($encodedText, $maxLength - $lookBack + $encodedCharPos + 1, 2);
                        $dec = hexdec($hex);
                        if ($dec < 128) { // Single byte character.
                            // If the encoded char was found at pos 0, it will fit
                            // otherwise reduce maxLength to start of the encoded char
                            $maxLength = ($encodedCharPos == 0) ? $maxLength :
                            $maxLength - ($lookBack - $encodedCharPos);
                            $foundSplitPos = true;
                        } elseif ($dec >= 192) { // First byte of a multi byte character
                            // Reduce maxLength to split at start of character
                            $maxLength = $maxLength - ($lookBack - $encodedCharPos);
                            $foundSplitPos = true;
                        } elseif ($dec < 192) { // Middle byte of a multi byte character, look further back
                            $lookBack += 3;
                        }
                    } else {
                        // No encoded character found
                        $foundSplitPos = true;
                    }
                }
                return $maxLength;
            }

            /**
            * Wraps message for use with mailers that do not
            * automatically perform wrapping and for quoted-printable.
            * Original written by philippe.
            * @param string $message The message to wrap
            * @param integer $length The line length to wrap to
            * @param boolean $qp_mode Whether to run in Quoted-Printable mode
            * @access public
            * @return string
            */
            public static function WrapText($message, $length, $charset, $qp_mode = false)
            {
                $soft_break = ($qp_mode) ? sprintf(" =%s", self::LE) : self::LE;

                // If utf-8 encoding is used, we will need to make sure we don't
                // split multibyte characters when we wrap
                $is_utf8 = (strtolower($charset) == "utf-8");

                $message = self::FixEOL($message);
                if (substr($message, -1) == self::LE) {
                    $message = substr($message, 0, -1);
                }

                $line = explode(self::LE, $message);
                $message = '';
                for ($i=0 ;$i < count($line); $i++) {
                    $line_part = explode(' ', $line[$i]);
                    $buf = '';

                    for ($e = 0; $e<count($line_part); $e++) {
                        $word = $line_part[$e];

                        if ($qp_mode && (strlen($word) > $length)) {
                            $space_left = $length - strlen($buf) - 1;

                            if ($e != 0) {
                                if ($space_left > 20) {
                                    $len = $space_left;
                                    if ($is_utf8) {
                                        $len = self::UTF8CharBoundary($word, $len);
                                    } elseif (substr($word, $len - 1, 1) == "=") {
                                        $len--;
                                    } elseif (substr($word, $len - 2, 1) == "=") {
                                        $len -= 2;
                                    }

                                    $part = substr($word, 0, $len);
                                    $word = substr($word, $len);
                                    $buf .= ' ' . $part;
                                    $message .= $buf . sprintf("=%s", self::LE);
                                } else {
                                    $message .= $buf . $soft_break;
                                }

                                $buf = '';
                            }

                            while (strlen($word) > 0) {
                                $len = $length;
                                if ($is_utf8) {
                                    $len = self::UTF8CharBoundary($word, $len);
                                } elseif (substr($word, $len - 1, 1) == "=") {
                                    $len--;
                                } elseif (substr($word, $len - 2, 1) == "=") {
                                    $len -= 2;
                                }

                                $part = substr($word, 0, $len);
                                $word = substr($word, $len);

                                if (strlen($word) > 0) {
                                    $message .= $part . sprintf("=%s", self::LE);
                                } else {
                                    $buf = $part;
                                }
                            }
                        } else {
                            $buf_o = $buf;
                            $buf .= ($e == 0) ? $word : (' ' . $word);

                            if (strlen($buf) > $length && $buf_o != '') {
                                $message .= $buf_o . $soft_break;
                                $buf = $word;
                            }
                        }
                    }
                    $message .= $buf . self::LE;
                }

                return $message;
            }

            /**
            * Correctly encodes and wraps long multibyte strings for mail headers
            * without breaking lines within a character.
            * Adapted from a function by paravoid at http://uk.php.net/manual/en/function.mb-encode-mimeheader.php
            * @access public
            * @param string $str multi-byte text to wrap encode
            * @return string
            */
            public static function Base64EncodeWrapMB($str, $charset)
            {
                $start = "=?".$charset."?B?";
                $end = "?=";
                $encoded = "";

                $mb_length = mb_strlen($str, $charset);
                // Each line must have length <= 75, including $start and $end
                $length = 75 - strlen($start) - strlen($end);
                // Average multi-byte ratio
                $ratio = $mb_length / strlen($str);
                // Base64 has a 4:3 ratio
                $offset = floor($length * $ratio * .75);

                for ($i = 0; $i < $mb_length; $i += $offset) {
                    $lookBack = 0;

                    do {
                        $offset = $offset - $lookBack;
                        $chunk = mb_substr($str, $i, $offset, $charset);
                        $chunk = base64_encode($chunk);
                        $lookBack++;
                    } while (strlen($chunk) > $length);

                    $encoded .= $chunk . self::LE;
                }

                // Chomp the last linefeed
                return substr($encoded, 0, -strlen(self::LE));
            }

            /**
            * Encode string to quoted-printable.
            * Only uses standard PHP, slow, but will always work
            * @access public
            * @param string $string the text to encode
            * @param integer $line_max Number of chars allowed on a line before wrapping
            * @return string
            */
            public static function EncodeQPphp($input = '', $line_max = 76, $space_conv = false)
            {
                $hex = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
                $lines = preg_split('/(?:\r\n|\r|\n)/', $input);
                $eol = "\r\n";
                $escape = '=';
                $output = '';
                while (list(, $line) = each($lines)) {
                    $linlen = strlen($line);
                    $newline = '';
                    for ($i = 0; $i < $linlen; $i++) {
                        $c = substr($line, $i, 1);
                        $dec = ord($c);
                        if (($i == 0) && ($dec == 46)) { // convert first point in the line into =2E
                            $c = '=2E';
                        }
                        if ($dec == 32) {
                            if ($i == ($linlen - 1) || $space_conv) { // convert space at eol only
                                $c = '=20';
                            }
                        } elseif (($dec == 61) || ($dec < 32) || ($dec > 126)) { // always encode "\t", which is *not* required
                            $h2 = floor($dec/16);
                            $h1 = floor($dec%16);
                            $c = $escape.$hex[$h2].$hex[$h1];
                        }
                        if ((strlen($newline) + strlen($c)) >= $line_max) { // CRLF is not counted
                            $output .= $newline.$escape.$eol; //  soft line break; " =\r\n" is okay
                            $newline = '';
                            // check if newline first character will be point or not
                            if ($dec == 46) {
                                $c = '=2E';
                            }
                        }
                        $newline .= $c;
                    } // end of for
                    $output .= $newline.$eol;
                } // end of while
                return $output;
            }

            /**
            * Encode string to RFC2045 (6.7) quoted-printable format
            * Uses a PHP5 stream filter to do the encoding about 64x faster than the old version
            * Also results in same content as you started with after decoding
            * @see EncodeQPphp()
            * @access public
            * @param string $string the text to encode
            * @param integer $line_max Number of chars allowed on a line before wrapping
            * @param boolean $space_conv Dummy param for compatibility with existing EncodeQP function
            * @return string
            * @author Marcus Bointon
            */
            public static function EncodeQP($string, $line_max = 76, $space_conv = false)
            {
                if (function_exists('quoted_printable_encode')) { //Use native function if it's available (>= PHP5.3)
                    return quoted_printable_encode($string);
                }

                $filters = stream_get_filters();
                if (!in_array('convert.*', $filters)) { //Got convert stream filter?
                    return self::EncodeQPphp($string, $line_max, $space_conv); //Fall back to old implementation
                }

                $fp = fopen('php://temp/', 'r+');
                $string = preg_replace('/\r\n?/', self::LE, $string); //Normalise line breaks
                $params = array('line-length' => $line_max, 'line-break-chars' => self::LE);
                $s = stream_filter_append($fp, 'convert.quoted-printable-encode', STREAM_FILTER_READ, $params);
                fputs($fp, $string);
                rewind($fp);
                $out = stream_get_contents($fp);
                stream_filter_remove($s);
                $out = preg_replace('/^\./m', '=2E', $out); //Encode . if it is first char on a line, workaround for bug in Exchange
                fclose($fp);
                return $out;
            }

            /**
            * Encode string to q encoding.
            * @link http://tools.ietf.org/html/rfc2047
            * @param string $str the text to encode
            * @param string $position Where the text is going to be used, see the RFC for what that means
            * @access public
            * @return string
            */
            public static function EncodeQ($str, $position = 'text')
            {
                // There should not be any EOL in the string
                $encoded = preg_replace('/[\r\n]*/', '', $str);

                switch (strtolower($position)) {
                    case 'phrase':
                        $encoded = preg_replace("/([^A-Za-z0-9!*+\/ -])/e", "'='.sprintf('%02X', ord('\\1'))", $encoded);
                        break;
                    case 'comment':
                        $encoded = preg_replace("/([\(\)\"])/e", "'='.sprintf('%02X', ord('\\1'))", $encoded);
                        // no break
                    case 'text':
                    default:
                        // Replace every high ascii, control =, ? and _ characters
                        $encoded = preg_replace_callback('/([\000-\011\013\014\016-\037\075\077\137\177-\377])/', function ($match) {
                            return '='.sprintf('%02X', ord($match[1]));
                        }, $encoded);
                        break;
                }

                // Replace every spaces to _ (more readable than =20)
                return str_replace(' ', '_', $encoded);
            }

            /**
            * Encodes string to requested format.
            * Returns an empty string on failure.
            * @param string $str The text to encode
            * @param string $encoding The encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
            * @access public
            * @return string
            */
            public static function EncodeString($str, $encoding = 'base64')
            {
                $encoded = '';
                switch (strtolower($encoding)) {
                    case 'base64':
                        $encoded = chunk_split(base64_encode($str), 76, self::LE);
                        break;
                    case '7bit':
                    case '8bit':
                        $encoded = self::FixEOL($str);
                        //Make sure it ends with a line break
                        if (substr($encoded, -(strlen(self::LE))) != self::LE) {
                            $encoded .= self::LE;
                        }
                        break;
                    case 'binary':
                        $encoded = $str;
                        break;
                    case 'quoted-printable':
                        $encoded = self::EncodeQP($str);
                        break;
                    default:
                        break;
                }
                return $encoded;
            }

            /**
            * Encode a header string to best (shortest) of Q, B, quoted or none.
            * @access public
            * @return string
            */
            public static function EncodeHeader($str, $position = 'text', $charset = 'utf-8')
            {
                $x = 0;

                switch (strtolower($position)) {
                    case 'phrase':
                        if (!preg_match('/[\200-\377]/', $str)) {
                            // Can't use addslashes as we don't know what value has magic_quotes_sybase
                            $encoded = addcslashes($str, "\0..\37\177\\\"");
                            if (($str == $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str)) {
                                return ($encoded);
                            } else {
                                return ("\"$encoded\"");
                            }
                        }
                        $x = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
                        break;
                    case 'comment':
                        $x = preg_match_all('/[()"]/', $str, $matches);
                        // Fall-through
                        // no break
                    case 'text':
                    default:
                        $x += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
                        break;
                }

                if ($x == 0) {
                    return ($str);
                }

                $maxlen = 75 - 7 - strlen($charset);
                // Try to select the encoding which should produce the shortest output
                if (strlen($str)/3 < $x) {
                    $encoding = 'B';
                    if (function_exists('mb_strlen') && self::CheckForMultbyte($str, $charset)) {
                        // Use a custom function which correctly encodes and wraps long
                        // multibyte strings without breaking lines within a character
                        $encoded = self::Base64EncodeWrapMB($str, $charset);
                    } else {
                        $encoded = base64_encode($str);
                        $maxlen -= $maxlen % 4;
                        $encoded = trim(chunk_split($encoded, $maxlen, "\n"));
                    }
                } else {
                    $encoding = 'Q';
                    $encoded = self::EncodeQ($str, $position);
                    $encoded = self::WrapText($encoded, $maxlen, $charset, true);
                    $encoded = str_replace('='.self::LE, "\n", trim($encoded));
                }

                $encoded = preg_replace('/^(.*)$/m', " =?".$charset."?$encoding?\\1?=", $encoded);
                return trim(str_replace("\n", self::LE, $encoded));
            }

            /**
            * Encodes attachment in requested format.
            * Returns an empty string on failure.
            * @param string $path The full path to the file
            * @param string $encoding The encoding to use; one of 'base64', '7bit', '8bit', 'binary', 'quoted-printable'
            * @see EncodeFile()
            * @access private
            * @return string
            */
            public static function EncodeFile($path, $encoding = 'base64')
            {
                try {
                    if (!is_readable($path)) {
                        throw new Exception("Can not open file ".$path);
                    }

                    if (function_exists('get_magic_quotes')) {
                        function get_magic_quotes()
                        {
                            return false;
                        }
                    }

                    return self::EncodeString(file_get_contents($path), $encoding);
                } catch (Exception $e) {
                    return '';
                }
            }

            /**
            * Returns the start of a message boundary.
            * @access private
            */
            public static function GetBoundaryBegin($boundary, $charSet, $contentType, $encoding)
            {
                $result = '';

                $result .= self::TextLine('--' . $boundary);
                $result .= sprintf("Content-Type: %s; charset = \"%s\"", $contentType, $charSet);
                $result .= self::LE;
                $result .= self::HeaderLine('Content-Transfer-Encoding', $encoding);
                return  $result. self::LE;
            }

            /**
            * Returns the end of a message boundary.
            * @access private
            */
            public static function GetBoundaryEnd($boundary)
            {
                return self::LE . '--' . $boundary . '--' . self::LE;
            }

            /**
            *  Returns a formatted header line.
            * @access public
            * @return string
            */
            public static function HeaderLine($name, $value)
            {
                return $name . ': ' . $value . self::LE;
            }

            /**
            * Returns a formatted mail line.
            * @access public
            * @return string
            */
            public static function TextLine($value)
            {
                return $value . self::LE;
            }

            public static function CheckForMultbyte($string, $encoding)
            {
                if (function_exists('mb_strlen')) {
                    return (strlen($string) > mb_strlen($string, $encoding));
                }
                return false;
            }

            public static function StripNewLines($string)
            {
                return trim(str_replace(self::LF, '', str_replace(self::CR, '', $string)));
            }
        }

    }
