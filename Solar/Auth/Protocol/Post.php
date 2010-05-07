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
class Solar_Auth_Protocol_Post extends Solar_Auth_Protocol {

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
    protected $_Solar_Auth_Protocol_Post = array(
        'source_handle'  => 'handle',
        'source_passwd'  => 'passwd',
        'source_process' => 'process',
        'source_redirect' => 'redirect',
        'process_login'  => null,
        'process_logout' => null,
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
        if (empty($this->_config['process_login'])) {
            $this->_config['process_login'] = $this->locale('PROCESS_LOGIN');
        }
        
        if (empty($this->_config['process_logout'])) {
            $this->_config['process_logout'] = $this->locale('PROCESS_LOGOUT');
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
        return $this->_request->post($this->_config['source_process']) == $this->_config['process_login'];
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
        if ($this->_request->isCsrf()) {
            return false;
        }
        return $this->_request->post($this->_config['source_process']) == $this->_config['process_logout'];
    }

    /**
     * 
     * Loads the user credentials (handle and passwd) from the request source.
     * 
     * @return void
     * 
     */
    protected function getCredentials()
    {
        // retrieve the handle and passwd
        $handle = $this->_request->post($this->_config['source_handle']);
        $passwd = $this->_request->post($this->_config['source_passwd']);
        
        return array('handle'=> $handle, 'passwd' => $passwd);
    }

    /**
     * 
     * Looks at the value of the 'redirect' source key, and determines a
     * redirection url from it.
     * 
     * If the 'redirect' key is empty or not present, will not redirect, and
     * processing will continue.
     * 
     * @return void
     * 
     */
    public function getLoginRedirect()
    {
        return $this->_request->post($this->_config['source_redirect']);
    }

    /**
     * 
     * Looks at the value of the 'redirect' source key, and determines a
     * redirection url from it.
     * 
     * If the 'redirect' key is empty or not present, will not redirect, and
     * processing will continue.
     * 
     * @return void
     * 
     */
    public function getLogoutRedirect()
    {
        return $this->_request->post($this->_config['source_redirect']);
    }

}
