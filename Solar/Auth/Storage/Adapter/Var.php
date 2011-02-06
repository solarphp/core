<?php
/**
 * 
 * Authenticate against username-password arrays.
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
class Solar_Auth_Storage_Adapter_Var extends Solar_Auth_Storage_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @config array data The credential data.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Storage_Adapter_Var = array(
        'data' => array(),
    );
    
    /**
     * 
     * The credential data.
     * 
     * @var array
     * 
     */
    protected $_data = array();
    
    /**
     * 
     * Post-construction tasks.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        $this->_data = (array) $this->_config['data'];
    }
    
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
        
        // is there a username key in the data array?
        if (! array_key_exists($handle, $this->_data)) {
            return false;
        }
        
        // does the plain-text password match?
        if ($this->_data[$handle] === $passwd) {
            // return the user data
            $user = array('handle' => $handle);
            return $user;
        } else {
            return false;
        }
    }
}
