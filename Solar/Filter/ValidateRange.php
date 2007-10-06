<?php
/**
 * 
 * Validates that a value is within a given range.
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
 * Validates that a value is within a given range.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 */
class Solar_Filter_ValidateRange extends Solar_Filter_Abstract {
    
    /**
     * 
     * Validates that the value is within a given range.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $min The minimum valid value.
     * 
     * @param mixed $max The maximum valid value.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateRange($value, $min, $max)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        return ($value >= $min && $value <= $max);
    }
}