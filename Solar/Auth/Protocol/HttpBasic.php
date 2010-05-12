<?php
/**
 * 
 * Abstract Authentication Protocol.
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
class Solar_Auth_Protocol_HttpBasic extends Solar_Auth_Protocol {

    /**
     * 
     * Default configuration values.
     * @config string source_handle Username key in the credential array source,
     *   default 'handle'.
     * 
     * @config string source_passwd Password key in the credential array source,
     *   default 'passwd'.
     * 
     * @config string source_redirect Element key in the credential array source to indicate
     *   where to redirect on successful login or logout, default 'redirect'.
     * 
     * @config string source_process Element key in the credential array source to indicate
     *   how to process the request, default 'process'.
     * 
     * @config string process_login The source_process element value indicating a login request;
     *   default is the 'PROCESS_LOGIN' locale key value.
     * 
     * @config string process_logout The source_process element value indicating a logout request;
     *   default is the 'PROCESS_LOGOUT' locale key value.
     *
     */
    protected $_Solar_Auth_Protocol_HttpBasic = array(
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
        $response->setHeader('WWW-Authenticate', 'Basic realm="secure area"');
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
        // HTTP Auth doesn't support redirecting after first authentication
        return null;
    }

}
