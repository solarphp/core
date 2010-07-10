<?php
/**
 * 
 * Abstract Authentication Login Protocol.
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
abstract class Solar_Auth_Login extends Solar_Base {

    /**
     * 
     * Details on the current request.
     * 
     * @var Solar_Request
     * 
     */
    protected $_request;

    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        
        // get the current request environment
        $this->_request = Solar_Registry::get('request');
    }

    /**
     * 
     * Tells if the current page load appears to be the result of
     * an attempt to log in.
     * 
     * @return bool
     * 
     */
    abstract public function isLoginRequest();

    /**
     * 
     * Loads the user credentials (handle and passwd) from the request source.
     * 
     * @return array List of authentication credentials
     * 
     */
    abstract public function getCredentials();

    /**
     * 
     * The login was success, complete the protocol
     * 
     * @return void
     * 
     */
    abstract public function postLoginSuccess();

    /**
     * 
     * The login was a failure, complete the protocol
     * 
     * @return void
     * 
     */
    abstract public function postLoginFailure();

    /**
     * 
     * Determine the location to redirect to after successful login
     * 
     * @return string|null The url to redirect to or null if no redirect
     * 
     */
    abstract public function getLoginRedirect();
    
}