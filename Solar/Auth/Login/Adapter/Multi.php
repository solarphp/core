<?php
/**
 * 
 * Authenticate against a stack of multiple adapters; first success wins.
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
class Solar_Auth_Login_Adapter_Multi extends Solar_Auth_Login_Adapter
{
    
    /**
     * 
     * Default configuration values.
     *
     */
    protected $_Solar_Auth_Login_Adapter_Multi = array(
        'adapters' => array(),
    );
    

    protected $_adapters;
    
    protected $_protocol;
    
    protected function _postConstruct()
    {
        $this->_adapters = (array) $this->_config['adapters'];
    }
    
    /**
     * 
     * Tells if the current page load appears to be the result of
     * an attempt to log in.
     * 
     * @return Solar_Auth_Login_Adapter
     * 
     */
    public function isLoginRequest()
    {
        // have we already set a protocol?
        if ($this->_protocol) {
            return $this->_protocol->isLoginRequest();
        }
        
        // go through the adapters to find a protocol
        foreach ($this->_adapters as $key => $spec) {
        
            // get the adapter protocol as a dependency
            $protocol = Solar::dependency('Solar_Auth_Login', $spec);
            
            // retain the dependency
            $this->_adapter[$key] = $protocol;
            
            // does it recognize a login request?
            if ($protocol->isLoginRequest()) {
                // yes, retain the protocol
                $this->_protocol = $protocol;
                return true;
            }
        }
        
        // no protocol found
        return null;
    }
    
    public function getProtocol()
    {
        return $this->_protocol;
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
        return $this->_protocol->getCredentials();
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
        $this->_protocol->postLoginSuccess();
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
        $this->_protocol->postLoginFailure();
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
        return $this->_protocol->getLoginRedirect();
    }
}
