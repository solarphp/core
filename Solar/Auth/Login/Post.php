<?php
/**
 * 
 * Login protocol based on receiving post parameters
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
class Solar_Auth_Login_Post extends Solar_Auth_Login {

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
     *   where to redirect on successful login, default 'redirect'.
     * 
     * @config string source_process Element key in the credential array source to indicate
     *   how to process the request, default 'process'.
     * 
     * @config string process The source_process element value indicating a login request;
     *   default is the 'PROCESS_LOGIN' locale key value.
     * 
     */
    protected $_Solar_Auth_Login_Post = array(
        'source_handle'  => 'handle',
        'source_passwd'  => 'passwd',
        'source_process' => 'process',
        'source_redirect' => 'redirect',
        'process'  => null,
    );

    /**
     * 
     * Modifies $this->_config after it has been built.
     * 
     * @return void
     * 
     */
    protected function _postConfig()
    {
        parent::_postConfig();
        
        // make sure we have process values
        if (empty($this->_config['process'])) {
            $this->_config['process'] = $this->locale('PROCESS_LOGIN');
        }
    }

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
        if ($this->_request->isCsrf()) {
            return false;
        }
        return $this->_request->post($this->_config['source_process']) == $this->_config['process'];
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
        $handle = $this->_request->post($this->_config['source_handle']);
        $passwd = $this->_request->post($this->_config['source_passwd']);
        
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
        return $this->_request->post($this->_config['source_redirect']);
    }

}
