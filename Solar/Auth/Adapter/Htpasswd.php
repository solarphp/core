<?php
/**
 * 
 * Authenticate against a file generated by htpasswd.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Authentication adapter class.
 */
Solar::loadClass('Solar_Auth_Adapter');

/**
 * 
 * Authenticate against a file generated by htpasswd.
 * 
 * Format for each line is "username:hashedpassword\n";
 * 
 * Automatically checks against DES, SHA, and apr1-MD5.
 * 
 * SECURITY NOTE: Default DES encryption will only check up to the first
 * 8 characters of a password; chars after 8 are ignored.  This means
 * that if the real password is "atechars", the word "atecharsnine" would
 * be valid.  This is bad.  As a workaround, if the password provided by
 * the user is longer than 8 characters, and DES encryption is being
 * used, this class will *not* validate it.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 */
class Solar_Auth_Adapter_Htpasswd extends Solar_Auth_Adapter {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * Keys are:
     * 
     * : \\file\\ : (string) Path to password file.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'file'  => null,
    );
    
    /**
     * 
     * Verifies a username handle and password.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    protected function _verify()
    {
        $handle = $this->_handle;
        $passwd = $this->_passwd;
        
        // force the full, real path to the file
        $file = realpath($this->_config['file']);
        
        // does the file exist?
        if (! file_exists($file)) {
            return $this->_exception(
                'ERR_FILE_NOT_FOUND',
                array('file' => $file)
            );
        }
        
        // open the file
        $fp = @fopen($file, 'r');
        if (! $fp) {
            return $this->_exception(
                'ERR_FILE_NOT_READABLE',
                array('file' => $file)
            );
        }
        
        // find the user's line in the file
        $len = strlen($handle) + 1;
        $ok = false;
        while ($line = fgets($fp)) {
            if (substr($line, 0, $len) == "$handle:") {
                // found the line, leave the loop
                $ok = true;
                break;
            }
        }
        
        // close the file
        fclose($fp);
        
        // did we find the username?
        if (! $ok) {
            // username not in the file
            return false;
        }
        
        // break up the pieces: 0 = handle, 1 = encrypted (hashed)
        // passwd. may be more than that but we don't care.
        $tmp = explode(':', trim($line));
        $stored_hash = $tmp[1];
        
        // what kind of encryption hash are we using?  look at the first
        // few characters of the hash to find out.
        if (substr($stored_hash, 0, 6) == '$apr1$') {
        
            // use the apache-specific MD5 encryption
            $computed_hash = self::_apr1($passwd, $stored_hash);
            
        } elseif (substr($stored_hash, 0, 5) == '{SHA}') {
        
            // use SHA1 encryption.  pack SHA binary into hexadecimal,
            // then encode into characters using base64. this is per
            // Tomas V. V. Cox.
            $hex = pack('H40', sha1($passwd));
            $computed_hash = '{SHA}' . base64_encode($hex);
            
        } else {
        
            // use DES encryption (the default).
            // 
            // Note that crypt() will only check up to the first 8
            // characters of a password; chars after 8 are ignored. This
            // means that if the real password is "atecharsnine", the
            // word "atechars" would be valid.  This is bad.  As a
            // workaround, if the password provided by the user is
            // longer than 8 characters, this method will *not* validate
            // it.
            //
            // is the password longer than 8 characters?
            if (strlen($passwd) > 8) {
                // automatically reject
                return false;
            } else {
                $computed_hash = crypt($passwd, $stored_hash);
            }
        }
        
        // did the hashes match?
        return $stored_hash == $computed_hash;
    }
    
    /**
     * 
     * APR compatible MD5 encryption.
     * 
     * @author Mike Wallner <mike@php.net>
     * 
     * @author Paul M. Jones (minor modfications) <pmjones@solarphp.com>
     * 
     * @param string $plain Plaintext to crypt.
     * 
     * @param string $salt The salt to use for encryption.
     * 
     * @return string The APR MD5 encrypted string.
     * 
     */
    protected static function _apr1($plain, $salt)
    {
        if (preg_match('/^\$apr1\$/', $salt)) {
            $salt = preg_replace('/^\$apr1\$([^$]+)\$.*/', '\\1', $salt);
        } else {
            $salt = substr($salt, 0,8);
        }
        
        $length  = strlen($plain);
        $context = $plain . '$apr1$' . $salt;
        
        $binary = md5($plain . $salt . $plain, true);
        
        for ($i = $length; $i > 0; $i -= 16) {
            $context .= substr($binary, 0, min(16 , $i));
        }
        for ( $i = $length; $i > 0; $i >>= 1) {
            $context .= ($i & 1) ? chr(0) : $plain[0];
        }
        
        $binary = md5($context, true);
        
        for($i = 0; $i < 1000; $i++) {
            $new = ($i & 1) ? $plain : $binary;
            if ($i % 3) {
                $new .= $salt;
            }
            if ($i % 7) {
                $new .= $plain;
            }
            $new .= ($i & 1) ? $binary : $plain;
            $binary = md5($new, true);
        }
        
        $p = array();
        for ($i = 0; $i < 5; $i++) {
            $k = $i + 6;
            $j = $i + 12;
            if ($j == 16) {
                $j = 5;
            }
            $p[] = self::_64(
                (ord($binary[$i]) << 16) |
                (ord($binary[$k]) << 8) |
                (ord($binary[$j])),
                5
            );
        }
        
        return '$apr1$' . $salt . '$' . implode($p) 
             . self::_64(ord($binary[11]), 3);
    }
    
    /**
     * 
     * Convert to allowed 64 characters for encryption.
     * 
     * @author Mike Wallner <mike@php.net>
     * 
     * @author Paul M. Jones (minor modfications) <pmjones@solarphp.com>
     * 
     * @param string $value The value to convert.
     * 
     * @param int $count The number of characters.
     * 
     * @return string The converted value.
     * 
     */
    protected static function _64($value, $count)
    {
        $charset = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $result = '';
        while(--$count) {
            $result .= $charset[$value & 0x3f];
            $value >>= 6;
        }
        return $result;
    }
}
?>