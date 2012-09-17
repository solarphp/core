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
     * @config array adapters An array of storage dependency objects, one for
     * each of the storage systems to be used.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Storage_Adapter_Multi = array(
        'adapters' => array(),
    );
    
    /**
     * 
     * An array of adapter dependencies, one for each of the storage systems
     * to be used.
     * 
     * @var array
     * 
     */
    protected $_adapters;
    
    /**
     * 
     * Post-construction tasks.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        $adapters = (array) $this->_config['adapters'];
        foreach ($adapters as $class) {
            $this->_adapters[] = Solar::factory($class);
            // alternatively use a Solar::dependency(); might be a better option
        }
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
            $result = $adapter->validateCredentials($credentials);
            if ($result) {
                return $result;
            }
        }
        
        return null;
    }
}
