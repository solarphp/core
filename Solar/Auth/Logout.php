<?php
/**
 * 
 * Logout protocol adapter factory.
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
class Solar_Auth_Logout extends Solar_Factory {
    
    /**
     * 
     * Default configuration values.
     * 
     * @config string adapter The adapter class, for example 'Solar_Access_Adapter_Open'.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Logout = array(
        'adapter' => 'Solar_Auth_Logout_Adapter_Post',
    );
}
