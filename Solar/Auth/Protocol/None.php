<?php
/**
 * 
 * Authenticate against nothing; defaults all authentication to "failed".
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
class Solar_Auth_Protocol_None extends Solar_Auth_Protocol
{

    /**
     * 
     * Default configuration values.
     *
     */
    protected $_Solar_Auth_Protocol_Get = array(
    );


    /**
     * 
     * Tells if the current page load appears to be the result of
     * an attempt to log in.
     * 
     * @return bool
     * 
     */
    public function isLoginRequest()
    {
        return false;
    }

    /**
     * 
     * Tells if the current page load appears to be the result of
     * an attempt to log out.
     * 
     * @return bool
     * 
     */
    public function isLogoutRequest()
    {
        return false;
    }

    /**
     * 
     * Loads the user credentials (handle and passwd) from the request source.
     * 
     * @return array List of authentication credentials
     * 
     */
    public function getCredentials()
    {
        return false;
    }

    /**
     * 
     * The login was success, complete the protocol
     * 
     * @return void
     * 
     */
    public function postLoginSuccess()
    {
    }

    /**
     * 
     * The login was a failure, complete the protocol
     * 
     * @return void
     * 
     */
    public function postLoginFailure()
    {
    }

    /**
     * 
     * Looks at the value of the 'redirect' source key, and determines a
     * redirection url from it.
     * 
     * If the 'redirect' key is empty or not present, will not redirect, and
     * processing will continue.
     * 
     * @return string|null The url to redirect to or null if no redirect
     * 
     */
    public function getLoginRedirect()
    {
        return null;
    }

    /**
     * 
     * Looks at the value of the 'redirect' source key, and determines a
     * redirection url from it.
     * 
     * If the 'redirect' key is empty or not present, will not redirect, and
     * processing will continue.
     * 
     * @return string|null The url to redirect to or null if no redirect
     * 
     */
    public function getLogoutRedirect()
    {
        return null;
    }
}
