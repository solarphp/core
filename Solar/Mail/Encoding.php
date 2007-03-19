<?php
/**
 * 
 * Static utility class for encoding mail message values.
 * 
 * Refactored and modified from Zend_Mime and related classes.
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
class Solar_Mail_Encoding extends Solar_Base {
    
    /**
     * 
     * Map of characters that quuoted-printable encoding should replace.
     * 
     * @var array
     * 
     */
    static public $qp_key = array(
        "\x00","\x01","\x02","\x03","\x04","\x05","\x06","\x07",
        "\x08","\x09","\x0A","\x0B","\x0C","\x0D","\x0E","\x0F",
        "\x10","\x11","\x12","\x13","\x14","\x15","\x16","\x17",
        "\x18","\x19","\x1A","\x1B","\x1C","\x1D","\x1E","\x1F",
        "\x7F","\x80","\x81","\x82","\x83","\x84","\x85","\x86",
        "\x87","\x88","\x89","\x8A","\x8B","\x8C","\x8D","\x8E",
        "\x8F","\x90","\x91","\x92","\x93","\x94","\x95","\x96",
        "\x97","\x98","\x99","\x9A","\x9B","\x9C","\x9D","\x9E",
        "\x9F","\xA0","\xA1","\xA2","\xA3","\xA4","\xA5","\xA6",
        "\xA7","\xA8","\xA9","\xAA","\xAB","\xAC","\xAD","\xAE",
        "\xAF","\xB0","\xB1","\xB2","\xB3","\xB4","\xB5","\xB6",
        "\xB7","\xB8","\xB9","\xBA","\xBB","\xBC","\xBD","\xBE",
        "\xBF","\xC0","\xC1","\xC2","\xC3","\xC4","\xC5","\xC6",
        "\xC7","\xC8","\xC9","\xCA","\xCB","\xCC","\xCD","\xCE",
        "\xCF","\xD0","\xD1","\xD2","\xD3","\xD4","\xD5","\xD6",
        "\xD7","\xD8","\xD9","\xDA","\xDB","\xDC","\xDD","\xDE",
        "\xDF","\xE0","\xE1","\xE2","\xE3","\xE4","\xE5","\xE6",
        "\xE7","\xE8","\xE9","\xEA","\xEB","\xEC","\xED","\xEE",
        "\xEF","\xF0","\xF1","\xF2","\xF3","\xF4","\xF5","\xF6",
        "\xF7","\xF8","\xF9","\xFA","\xFB","\xFC","\xFD","\xFE",
        "\xFF"
    );
    
    /**
     * 
     * Map of replacement characters for quoted-printable encoding.
     * 
     * @var array
     * 
     */
    static public $qp_val = array(
        "=00","=01","=02","=03","=04","=05","=06","=07",
        "=08","=09","=0A","=0B","=0C","=0D","=0E","=0F",
        "=10","=11","=12","=13","=14","=15","=16","=17",
        "=18","=19","=1A","=1B","=1C","=1D","=1E","=1F",
        "=7F","=80","=81","=82","=83","=84","=85","=86",
        "=87","=88","=89","=8A","=8B","=8C","=8D","=8E",
        "=8F","=90","=91","=92","=93","=94","=95","=96",
        "=97","=98","=99","=9A","=9B","=9C","=9D","=9E",
        "=9F","=A0","=A1","=A2","=A3","=A4","=A5","=A6",
        "=A7","=A8","=A9","=AA","=AB","=AC","=AD","=AE",
        "=AF","=B0","=B1","=B2","=B3","=B4","=B5","=B6",
        "=B7","=B8","=B9","=BA","=BB","=BC","=BD","=BE",
        "=BF","=C0","=C1","=C2","=C3","=C4","=C5","=C6",
        "=C7","=C8","=C9","=CA","=CB","=CC","=CD","=CE",
        "=CF","=D0","=D1","=D2","=D3","=D4","=D5","=D6",
        "=D7","=D8","=D9","=DA","=DB","=DC","=DD","=DE",
        "=DF","=E0","=E1","=E2","=E3","=E4","=E5","=E6",
        "=E7","=E8","=E9","=EA","=EB","=EC","=ED","=EE",
        "=EF","=F0","=F1","=F2","=F3","=F4","=F5","=F6",
        "=F7","=F8","=F9","=FA","=FB","=FC","=FD","=FE",
        "=FF"
    );
    
    /**
     * 
     * A string representation of the $qp_keys property for strcspn() use.
     * 
     * @var string
     * 
     */
    static public $qp_str = 
         "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\x7F\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8A\x8B\x8C\x8D\x8E\x8F\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9A\x9B\x9C\x9D\x9E\x9F\xA0\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xA9\xAA\xAB\xAC\xAD\xAE\xAF\xB0\xB1\xB2\xB3\xB4\xB5\xB6\xB7\xB8\xB9\xBA\xBB\xBC\xBD\xBE\xBF\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xDB\xDC\xDD\xDE\xDF\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF0\xF1\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF";
    
    /**
     * 
     * Is a text string already quoted-printable?
     * 
     * @param string $text The string to check.
     * 
     * @return bool True is already quoted-printable, false if not.
     * 
     */
    static public function isQuotedPrintable($text)
    {
        return ! (strcspn($text, self::$qp_str) == strlen($text));
    }
    
    /**
     * 
     * Sanitizes header labels.
     * 
     * @var string $label The header label sanitize.
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
     * Encodes header values if they are not quoted-printable safe.
     * 
     * @param string $value The header value to encode.
     * 
     * @return string The encoded header value.
     * 
     */
    static public function headerValue($value)
    {
        if (self::isQuotedPrintable($value)) {
            $value = self::quotedPrintable($value);
            $value = str_replace('?', '=3F', $value);
            return '=?' . $this->_charset . '?Q?' . $value . '?=';
        } else {
            return $value;
        }
    }
    
    /**
     * 
     * Applies "quoted-printable" encoding to text.
     * 
     * @param string $text The text to encode.
     * 
     * @param string $crlf The line-ending to use; default "\r\n".
     * 
     * @param int $len Break lines at this length; default 74.
     * 
     * @return string The encoded text.
     * 
     */
    static public function quotedPrintable($text, $crlf = "\r\n", $len = 74)
    {
        $out = '';
        $text = str_replace('=', '=3D', $text);
        $text = str_replace(self::$qp_key, self::$qp_val, $text);
        
        // Split encoded text into separate lines
        while ($text) {
            $ptr = strlen($text);
            if ($ptr > $len) {
                $ptr = $len;
            }
            
            // Ensure we are not splitting across an encoded character
            if (($pos = strrpos(substr($text, 0, $ptr), '=')) >= $ptr - 2) {
                $ptr = $pos;
            }
            
            // Check if there is a space at the end of the line and rewind
            if ($text[$ptr - 1] == ' ') {
                --$ptr;
            }
            
            // Add string and continue
            $out .= substr($text, 0, $ptr) . '=' . $crlf;
            $text = substr($text, $ptr);
        }
        
        $out = rtrim($out, $crlf);
        $out = rtrim($out, '=');
        return $out;
    }
    
    /**
     * 
     * Applies "base64" encoding to text.
     * 
     * @param string $text The text to encode.
     * 
     * @param string $crlf The line-ending to use; default "\r\n".
     * 
     * @param int $len Break lines at this length; default 74.
     * 
     * @return string The encoded text.
     * 
     */
    static public function base64($text, $crlf = "\r\n", $len = 74)
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
