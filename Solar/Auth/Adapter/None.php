<?php
/**
 * 
 * Authenticate against nothing; defaults all authentication to "failed."
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: None.php 1246 2006-06-06 21:49:31Z pmjones $
 * 
 */

/**
 * Authentication adapter class.
 */
Solar::loadClass('Solar_Auth_Adapter');

/**
 * 
 * Authenticate against nothing; defaults all authentication to "failed."
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 */
class Solar_Auth_Adapter_None extends Solar_Auth_Adapter {
    
    /**
     * 
     * Verifies a username handle and password.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    protected function _verify()
    {
        return false;
    }
}
?>