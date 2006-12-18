<?php
/**
 * 
 * Adapter to fetch roles from no source at all; always returns an empty array.
 * 
 * @category Solar
 * 
 * @package Solar_Role
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Abstract role adapter class.
 */
Solar::loadClass('Solar_Role_Adapter');

/**
 * 
 * Adapter to fetch roles from no source at all; always returns an empty array.
 * 
 * @category Solar
 * 
 * @package Solar_Role
 * 
 */
class Solar_Role_Adapter_None extends Solar_Role_Adapter {
    
    /**
     * 
     * Fetch the roles.
     * 
     * @param string $handle User handle to get roles for.
     * 
     * @return array An array of discovered roles.
     * 
     */
    public function fetch($handle)
    {
        return array();
    }
}
