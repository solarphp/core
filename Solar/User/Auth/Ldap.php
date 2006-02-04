<?php
/**
 * 
 * Authenticate against an LDAP server.
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
 * Authenticate against an LDAP server.
 *
 * @category Solar
 * 
 * @package Solar_User
 * 
 */
class Solar_User_Auth_Ldap extends Solar_Base {
    
    /**
     * 
     * User-supplied configuration values.
     * 
     * Keys are:
     * 
     * url => (string) URL to the LDAP server, e.g. "ldaps://example.com:389".
     * 
     * format => (string) Sprintf() format string for the LDAP query; %s
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
     * Validate a username and password.
     * 
     * @param string $user Username to authenticate.
     * 
     * @param string $pass The plain-text password to use.
     * 
     * @return boolean True on success, false on failure.
     * 
     */
    public function valid($username, $password)
    {
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
        $rdn = sprintf($this->_config['format'], $username);
        $bind = @ldap_bind($conn, $rdn, $password);
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