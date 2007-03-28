<?php
/**
 * 
 * Static utility class for encoding mail message values.
 * 
 * @category Solar
 * 
 * @package Solar_Mail
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Utility class for encoding mail message values.
 * 
 * @category Solar
 * 
 * @package Solar_Mail
 * 
 */
class Solar_Mail_Encoding {
    
    /**
     * 
     * Sanitizes header labels.
     * 
     * @param string $label The header label sanitize.
     * 
     * @return string The sanitized header label.
     * 
     */
    static public function headerLabel($label)
    {
        $label = preg_replace('/^[a-zA-Z-]/', '', $label);
        $label = ucwords(strtolower(str_replace('-', ' ', $label)));
        $label = str_replace(' ', '-', $label);
        return $label;
    }
    
    /**
     * 
     * Encodes a header value as per RFC 2047.
     * 
     * Copied, with modifications, from the "mime.php" class in the
     * [PEAR Mail_Mime](http://pear.php.net/Mail_Mime) package (v 1.62).
     * 
     * @author Richard Heyes  <richard@phpguru.org>
     * 
     * @author Tomas V.V. Cox <cox@idecnet.com>
     * 
     * @author Cipriano Groenendal <cipri@php.net>
     * 
     * @author Sean Coates <sean@php.net>
     * 
     * @param string $label The (sanitized) header label; needed for line
     * length calculations.
     * 
     * @param string $value The header value to encode.
     * 
     * @param string $charset The character set to note when encoding.
     * 
     * @param string $crlf The CRLF line-ending string.
     * 
     * @param string $len The line-length to wrap at.
     * 
     * @return string The encoded header value.
     * 
     */
    static public function headerValue($label, $value, $charset,
        $crlf = "\r\n", $len = 75)
    {
        $hdr_vals  = preg_split("/(\s)/", $value, -1, PREG_SPLIT_DELIM_CAPTURE);
        $value_out = "";
        $previous  = "";
        foreach ($hdr_vals as $hdr_val) {
            
            if (! trim($hdr_val)) {
                // whitespace needs to be handled with another string, or it
                // won't show between encoded strings. Prepend this to the next item.
                $previous .= $hdr_val;
                continue;
            } else {
                $hdr_val = $previous . $hdr_val;
                $previous = "";
            }
            
            // any non-ascii characters?
            if (preg_match('/[\x80-\xFF]{1}/', $hdr_val)){
                
                // This header contains non ASCII chars and should be encoded
                // using quoted-printable. Dynamically determine the maximum
                // length of the strings.
                $prefix = '=?' . $charset . '?Q?';
                $suffix = '?=';
                
                // The -2 is here so the later regexp doesn't break any of
                // the translated chars. The -2 on the first line-regexp is
                // to compensate for the ": " between the header-name and the
                // header value.
                $maxLength        = $len - strlen($prefix . $suffix) - 2;
                $maxLength1stLine = $maxLength - strlen($label) - 2;
                
                // Replace all special characters used by the encoder.
                $search  = array("=",   "_",   "?",   " ");
                $replace = array("=3D", "=5F", "=3F", "_");
                $hdr_val = str_replace($search, $replace, $hdr_val);
                
                // Replace all extended characters (\x80-xFF) with their
                // ASCII values.
                $hdr_val = preg_replace_callback(
                    '/([\x80-\xFF])/',
                    array('Solar_Message_Encoding', '_qpReplace'),
                    $hdr_val
                );
                
                // This regexp will break QP-encoded text at every $maxLength
                // but will not break any encoded letters.
                $reg1st = "|(.{0,$maxLength1stLine})[^\=]|";
                $reg2nd = "|(.{0,$maxLength})[^\=]|";
                
                // Begin with the regexp for the first line.
                $reg = $reg1st;
                
                // Prevent lines that are just way too short.
                if ($maxLength1stLine > 1){
                    $reg = $reg2nd;
                }
                
                $output = "";
                while ($hdr_val) {
                    // Split translated string at every $maxLength.
                    // Make sure not to break any translated chars.
                    $found = preg_match($reg, $hdr_val, $matches);
                    
                    // After this first line, we need to use a different
                    // regexp for the first line.
                    $reg = $reg2nd;
                    
                    // Save the found part and encapsulate it in the
                    // prefix & suffix. Then remove the part from the
                    // $hdr_val variable.
                    if ($found){
                        $part    = $matches[0];
                        $hdr_val = substr($hdr_val, strlen($matches[0]));
                    }else{
                        $part    = $hdr_val;
                        $hdr_val = "";
                    }
                    
                    // RFC 2047 specifies that any split header should be seperated
                    // by a CRLF SPACE. 
                    if ($output){
                        $output .= "$crlf ";
                    }
                    
                    $output .= $prefix . $part . $suffix;
                }
                
                $hdr_val = $output;
            }
            
            $value_out .= $hdr_val;
        }
        
        return $value_out;
    }
    
    /**
     * 
     * Callback from the headerValue() method.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The value of $matches[0] with non-ASCII characters
     * encoded for quoted-printable.
     * 
     */
    static protected function _qpReplace($matches)
    {
        return '=' . strtoupper(dechex(ord($matches[1])));
    }
    
    /**
     * 
     * Applies "quoted-printable" encoding to text.
     * 
     * Code taken from <http://us3.php.net/manual/en/ref.stream.php/70826>.
     * 
     * @param string $text The text to encode.
     * 
     * @param string $crlf The line-ending to use; default "\r\n".
     * 
     * @param int $len Break lines at this length; default 75.
     * 
     * @return string The encoded text.
     * 
     */
    static public function quotedPrintable($text, $crlf = "\r\n", $len = 75)
    {
        // open a "temp" stream pointer
        $fp = fopen('php://temp/', 'r+');
        
        // make sure we break at $len lines with $crlf line-endings
        $params = array('line-length' => $len, 'line-break-chars' => $crlf);
        stream_filter_append(
            $fp,
            'convert.quoted-printable-encode',
            STREAM_FILTER_READ,
            $params
        );
        
        // put the text into the stream
        fputs($fp, $text);
        
        // rewind the pointer and retrieve the the content, which will
        // encode as it reads
        rewind($fp);
        $output = stream_get_contents($fp);
        
        // close the stream and return
        fclose($fp);
        return $output;
    }
    
    /**
     * 
     * Applies "base64" encoding to text.
     * 
     * @param string $text The text to encode.
     * 
     * @param string $crlf The line-ending to use; default "\r\n".
     * 
     * @param int $len Break lines at this length; default 75.
     * 
     * @return string The encoded text.
     * 
     */
    static public function base64($text, $crlf = "\r\n", $len = 75)
    {
        return rtrim(chunk_split(base64_encode($text), $len, $crlf));
    }
    
    /**
     * 
     * Applies the requested encoding to a text string.
     * 
     * @param string $type The type of encoding to use; '7bit', '8bit',
     * 'base64', or 'quoted-printable'.
     * 
     * @param string $text The text to encode.
     * 
     * @param string $crlf The line-ending to use; default "\r\n".
     * 
     * @return string The encoded text.
     * 
     */
    static public function apply($type, $text, $crlf)
    {
        switch (strtolower($type)) {
        case 'base64':
            return self::base64($text, $crlf);
            break;
        
        case 'quoted-printable':
            return self::quotedPrintable($text, $crlf);
            break;
        
        case '7bit':
        case '8bit':
            return $text;
            break;
        
        default:
            throw $this->_exception('ERR_UNKNOWN_TYPE');
            break;
        }
    }
}
