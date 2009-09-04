<?php
/**
 * 
 * Factory to return an HTTP request adapter instance.
 * 
 * @category Solar
 * 
 * @package Solar_Http
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Http_Request extends Solar_Factory
{
    /**
     * 
     * Default configuration values.
     * 
     * @config string adapter The adapter class; for example, 'Solar_Http_Request_Adapter_Stream'
     *   (the default).  When the `curl` extension is loaded, the default is
     *   'Solar_Http_Request_Adapter_Curl'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Http_Request = array(
        'adapter' => 'Solar_Http_Request_Adapter_Stream',
    );
    
    /**
     * 
     * Sets the default adapter to 'Solar_Http_Request_Adapter_Curl' when the
     * curl extension is available.
     * 
     * @return void
     * 
     */
    protected function _preConfig()
    {
        parent::_preConfig();
        if (extension_loaded('curl')) {
            $this->_Solar_Http_Request['adapter'] = 'Solar_Http_Request_Adapter_Curl';
        }
    }
}