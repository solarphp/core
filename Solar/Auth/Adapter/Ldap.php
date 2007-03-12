<?php
/**
 * 
 * Authenticate against an LDAP server.
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
 * Authenticate against an LDAP server.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 */
class Solar_Auth_Adapter_Ldap extends Solar_Auth_Adapter {
    
    /**
     * 
     * User-supplied configuration values.
     * 
     * Keys are ...
     * 
     * `uri`
     * : (string) URL to the LDAP server, for example "ldaps://example.com:389".
     * 
     * `format`
     * : (string) Sprintf() format string for the LDAP query; %s
     *   represents the username.  Example: "uid=%s,dc=example,dc=com".
     * 
     * `filter`
     * : (string) A regular-expression snippet that lists allowed characters
     *   in the username.  This is to help prevent LDAP injections.  Default
     *   allowed chars are 'a-zA-Z0-9_'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Adapter_Ldap = array(
        'uri'    => null,
        'format' => null,
        'filter' => 'a-zA-Z0-9_',
    );
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration.
     * 
     */
    public function __construct($config = null)
    {
        // make sure we have LDAP available
        if (! extension_loaded('ldap')) {
            throw $this->_exception(
                'ERR_EXTENSION_NOT_LOADED',
                array('extension' => 'ldap')
            );
        }
        
        // continue construction
        parent::__construct($config);
    }


    /**
     * 
     * Verifies a username handle and password.
     * 
     * @return mixed An array of verified user information, or boolean false
     * if verification failed.
     * 
     * 
     */
    protected function _processLogin()
    {
        // connect
        $conn = @ldap_connect($this->_config['uri']);
        
        // did the connection work?
        if (! $conn) {
            throw $this->_exception(
                'ERR_CONNECTION_FAILED',
                array($this->_config)
            );
        }
        
        // upgrade to LDAP3 when possible
        @ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        
        // filter the handle to prevent LDAP injection
        $regex = '/[^' . $this->_config['filter'] . ']/';
        $this->_handle = preg_replace($regex, '', $this->_handle);
        
        // bind to the server
        $rdn = sprintf($this->_config['format'], $this->_handle);
        $bind = @ldap_bind($conn, $rdn, $this->_passwd);
        
        // did the bind succeed?
        if ($bind) {
            ldap_close($conn);
            return array('handle' => $this->_handle);
        } else {
            $this->_err = @ldap_errno($conn) . " " . @ldap_error($conn);
            ldap_close($conn);
            return false;
        }
    }
}
