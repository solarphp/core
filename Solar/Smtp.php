<?php
/**
 * 
 * Factory class for SMTP connections.
 * 
 * @category Solar
 * 
 * @package Solar_Smtp
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Smtp extends Solar_Base {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are ...
     * 
     * `adapter`
     * : (string) The class to factory, for example 'Solar_Smtp_Adapter_NoAuth'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Smtp = array(
        'adapter' => 'Solar_Smtp_Adapter_NoAuth',
    );
    
    /**
     * 
     * Factory method to create SMTP adapter objects.
     * 
     * @return Solar_Smtp_Adapter
     * 
     */
    public function solarFactory()
    {
        $class = $this->_config['adapter'];
        unset($this->_config['adapter']);
        return Solar::factory($class, $this->_config);
    }
}