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
     * Keys are:
     * 
     * : \\url\\ : (string) URL to the LDAP server, e.g. "ldaps://example.com:389".
     * 
     * : \\format\\ : (string) Sprintf() format string for the LDAP query; %s
     * represents the username.  Example: "uid=%s,dc=example,dc=com".
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'url'    => null,
        'format' => null,
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
     * @return bool True if valid, false if not.
     * 
     */
    protected function _verify()
    {
        $handle = $this->_handle;
        $passwd = $this->_passwd;
        
        // connect
        $conn = @ldap_connect($this->_config['url']);
        
        // did the connection work?
        if (! $conn) {
            throw $this->_exception(
                'ERR_CONNECTION_FAILED',
                array($this->_config)
            );
        }
        
        // upgrade to LDAP3 when possible
        @ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        
        // bind to the server
        $rdn = sprintf($this->_config['format'], $handle);
        $bind = @ldap_bind($conn, $rdn, $passwd);
        ldap_close($conn);
        
        // return the bind-value
        if (! $bind) {
            // not using $this->_exception() because we need fine control
            // over the error text
            throw Solar::exception(
                get_class($this),
                @ldap_errno($conn),
                @ldap_error($conn),
                array($this->_config)
            );
        } else {
            return true;
        }
    }
}
?>