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
class Solar_Auth_Storage_Adapter_Multi extends Solar_Auth_Storage_Adapter
{
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Storage_Adapter_Multi = array(
        'adapters' => array(),
    );
    
    protected $_adapters;
    
    protected function _postConstruct()
    {
        $this->_adapters = (array) $this->_config['adapters'];
    }
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
        foreach ($this->_adapters as $adapter) {
            $result = $adapter->validateCredentials();
            if ($result) {
                return $result;
            }
        }
        
        return null;
    }
}
