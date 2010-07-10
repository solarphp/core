<?php
/**
 * 
 * Login protocol implementing HTTP Auth Login.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Adapter.php 4533 2010-04-23 16:35:15Z pmjones $
 * 
 */
class Solar_Auth_Login_HttpBasic extends Solar_Auth_Login {

    /**
     * 
     * Default configuration values.
     * @config string realm Realm to use in the auth challenge
     *
     */
    protected $_Solar_Auth_Login_HttpBasic = array(
        'realm' => 'Secure Area',
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
        // The nature of Basic HTTP Auth is that every request is a login
        // request and any failure to transmit authentication information
        // results in an access rejection

        if (!$this->_request->server('PHP_AUTH_USER')) {
            $this->postLoginFailure();
        }
        return true;
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
        // retrieve the handle and passwd
        $handle = $this->_request->server('PHP_AUTH_USER');
        $passwd = $this->_request->server('PHP_AUTH_PW');
        
        return array('handle'=> $handle, 'passwd' => $passwd);
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
        $response = Solar_Registry::get('response');
        $response->setHeader('WWW-Authenticate', 'Basic realm="' . $this->_config['realm'] . '"');
        $response->setStatusCode(401);
        $response->display();
        exit(0);
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
        // HTTP Auth doesn't support redirecting after first authentication
        return null;
    }

}
