<?php
/**
 * 
 * Validates that a value is an ISO 8601 time string.
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
 * Validates that a value is an ISO 8601 time string.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 */
class Solar_Filter_ValidateIsoTime extends Solar_Filter_Abstract {
    
    /**
     * 
     * Validates that the value is an ISO 8601 time string (hh:ii::ss format).
     * 
     * Per note from Chris Drozdowski about ISO 8601, allows two
     * midnight times ... 00:00:00 for the beginning of the day, and
     * 24:00:00 for the end of the day.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateIsoTime($value)
    {
        $expr = '/^(([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/D';
        
        return $this->_filter->validatePregMatch($value, $expr) ||
               ($value == '24:00:00');
    }
}