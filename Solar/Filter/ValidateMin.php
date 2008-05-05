<?php
/**
 * 
 * Validates that a value is greater than or equal to a minimum.
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
 * Validates that a value is greater than or equal to a minimum.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 */
class Solar_Filter_ValidateMin extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value is greater than or equal to a minimum.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $min The minimum valid value.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateMin($value, $min)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        return is_numeric($value) && $value >= $min;
    }
}