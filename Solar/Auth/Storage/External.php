<?php
/**
 * 
 * Verifies that the credentials passed were verified by a third 
 * Party identity provider, such as typekey, facebook, open id, or
 * SAML.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Htpasswd.php 4577 2010-05-16 00:01:03Z jeffmoore $
 * 
 */
class Solar_Auth_Storage_External extends Solar_Auth_Storage
{
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Storage_External = array(
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
        error_log('hello');
        if (!empty($credentials['verified'])) {
            return $credentials;
        } else {
            return false;
        }
    }
}
