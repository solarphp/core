<?php
/**
 * 
 * Authenticate against .ini style files (not very secure).
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
 * Authenticate against .ini style files.
 * 
 * Each group is a user handle, with keys for 'passwd', 'moniker', 'email',
 * and 'uri'.  For example:
 * 
 * <code>
 * [pmjones]
 * passwd = plaintextpass
 * email = pmjones@solarphp.com
 * moniker = Paul M. Jones
 * uri = http://paul-m-jones.com/
 * }}
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 */
class Solar_Auth_Adapter_Ini extends Solar_Auth_Adapter {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * Keys are ...
     * 
     * `file`:
     * (string) Path to .ini file.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Adapter_Ini = array(
        'file' => null,
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
        
        // force the full, real path to the .ini file
        $file = realpath($this->_config['file']);
        
        // does the file exist?
        if (! file_exists($file) || ! is_readable($file)) {
            throw $this->_exception(
                'ERR_FILE_NOT_READABLE',
                array('file' => $file)
            );
        }
        
        // parse the file into an array
        $data = parse_ini_file($file, true);
        
        // get user info for the handle
        $user = (! empty($data[$handle])) ? $data[$handle] : array();
        
        // there must be an entry for the username,
        // and the plain-text password must match.
        if (! empty($user['passwd']) && $user['passwd'] == $passwd) {
            // set additional values
            $this->_moniker  = (! empty($user['moniker']))  ? $user['moniker']  : null;
            $this->_email = (! empty($user['email'])) ? $user['email'] : null;
            $this->_uri   = (! empty($user['uri']))   ? $user['uri']   : null;
            return true;
        } else {
            return false;
        }
    }
}
?>