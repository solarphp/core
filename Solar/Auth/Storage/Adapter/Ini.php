<?php
/**
 * 
 * Authenticate against .ini style files.
 * 
 * Each group is a user handle, with keys for 'passwd', 'moniker', 'email',
 * and 'uri'.  For example ...
 * 
 *     [pmjones]
 *     passwd = plaintextpass
 *     email = pmjones@solarphp.com
 *     moniker = Paul M. Jones
 *     uri = http://paul-m-jones.com/
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
class Solar_Auth_Storage_Adapter_Ini extends Solar_Auth_Storage_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string file Path to .ini file.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Storage_Adapter_Ini = array(
        'file' => null,
    );
    
    
    /**
     * 
     * Verifies set of credentials.
     *
     * @param array $credentials A list of credentials to verify
     * 
     * @return mixed An array of verified user information, or boolean false
     * if verification failed.
     * 
     */
    public function validateCredentials($credentials)
    {
    
        if (empty($credentials['handle'])) {
            return false;
        }
        if (empty($credentials['passwd'])) {
            return false;
        }
        $handle = $credentials['handle'];
        $passwd = $credentials['passwd'];
    
        // force the full, real path to the .ini file
        $file = realpath($this->_config['file']);
        
        // does the file exist?
        if (! file_exists($file) || ! is_readable($file)) {
            throw $this->_exception('ERR_FILE_NOT_READABLE', array(
                'file' => $file,
            ));
        }
        
        // parse the file into an array
        $data = parse_ini_file($file, true);
        
        // get user info for the handle
        $user = (! empty($data[$handle])) ? $data[$handle] : array();
        
        // there must be an entry for the username,
        // and the plain-text password must match.
        if (! empty($user['passwd']) && $user['passwd'] == $passwd) {
            // insert the handle, and get rid of the password
            $user['handle'] = $handle;
            unset($user['passwd']);
            return $user;
        } else {
            return false;
        }
    }
}
