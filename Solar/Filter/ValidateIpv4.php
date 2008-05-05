<?php
/**
 * 
 * Validates that a value is ___.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Validates that a value is ___.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 */
class Solar_Filter_ValidateIpv4 extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value is a legal IPv4 address.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateIpv4($value)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        $result = ip2long($value);
        if ($result == -1 || $result === false) {
            return false;
        } else {
            return true;
        }
    }
}