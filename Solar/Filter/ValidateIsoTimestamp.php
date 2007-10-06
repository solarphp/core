<?php
/**
 * 
 * Validates that a value is an ISO 8601 timestamp string.
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
 * Validates that a value is an ISO 8601 timestamp string.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 */
class Solar_Filter_ValidateIsoTimestamp extends Solar_Filter_Abstract {
    
    /**
     * 
     * Validates that the value is an ISO 8601 timestamp string.
     * 
     * The format is "yyyy-mm-ddThh:ii:ss" (note the literal "T" in the
     * middle, which acts as a separator -- may also be a space).
     * 
     * Also checks that the date itself is valid (for example, no Feb 30).
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateIsoTimestamp($value)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        // correct length?
        if (strlen($value) != 19) {
            return false;
        }
        
        // valid date?
        $date = substr($value, 0, 10);
        if (! $this->_filter->validateIsoDate($date)) {
            return false;
        }
        
        // valid separator?
        $sep = substr($value, 10, 1);
        if ($sep != 'T' && $sep != ' ') {
            return false;
        }
        
        // valid time?
        $time = substr($value, 11, 8);
        if (! $this->_filter->validateIsoTime($time)) {
            return false;
        }
        
        // must be ok
        return true;
    }
}