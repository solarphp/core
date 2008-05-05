<?php
/**
 * 
 * Factory class for mail transport adapters.
 * 
 * @category Solar
 * 
 * @package Solar_Mail
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Mail_Transport extends Solar_Base
{
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are ...
     * 
     * `adapter`
     * : (string) The class to factory.  Default is
     * 'Solar_Mail_Transport_Adapter_Phpmail'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Mail_Transport = array(
        'adapter' => 'Solar_Mail_Transport_Adapter_Phpmail',
    );
    
    /**
     * 
     * Factory method to create transport adapter objects.
     * 
     * @return Solar_Mail_Transport_Adapter
     * 
     */
    public function solarFactory()
    {
        $class = $this->_config['adapter'];
        unset($this->_config['adapter']);
        return Solar::factory($class, $this->_config);
    }
}