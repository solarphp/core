<?php
/**
 * 
 * Authenticate against .ini style files (not very secure).
 * 
 * @category Solar
 * 
 * @package Solar_User
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Authenticate against .ini style files.
 * 
 * Format for each line is "username = plainpassword\n";
 *
 * @category Solar
 * 
 * @package Solar_User
 * 
 */
class Solar_User_Auth_Ini extends Solar_Base {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * Keys:
     * 
     * file => (string) Path to password file.
     * 
     * group => (string) The group in which usernames reside.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'file' => null,
        'group' => 'users',
    );


    /**
     * 
     * Validate a username and password.
     *
     * @param string $user Username to authenticate.
     * 
     * @param string $pass The plain-text password to use.
     * 
     * @return boolean True on success, false on failure.
     * 
     */
    public function valid($user, $pass)
    {
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
        
        // get a list of users from the [users] group
        $list = (array) $data[$this->_config['group']];
        
        // by default the user is not valid
        $valid = false;
        
        // there must be an entry for the username,
        // and the plain-text password must match.
        if (! empty($list[$user]) && $list[$user] == $pass) {
            $valid = true;
        }
        
        // done!
        return $valid;
    }
}
?>