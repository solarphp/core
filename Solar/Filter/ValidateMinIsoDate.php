<?php
/**
 * Validates that an ISO 8601 is at least a certain date.
 * 
 * @category Solar
 *
 * @package Solar_Filter
 *
 * @author Bahtiar `kalkin` Gadimov <bahtiar@gadimov.de>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $id$
 */
class Solar_Filter_ValidateMinIsoDate extends Solar_Filter_ValidateIsoTimestamp
{
    /**
     * 
     * Validates that an ISO 8601 is at least a certain date.
     * 
     * The format is "yyyy-mm-dd". Does not check if the date is a valide ISO
     * 8601 use Solar_Filter_ValidateIsoDate for that.
     * 
     * @param mixed $value The value to validate.
     * 
     * @param mixed $minDate The value must be at least this date.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateMinIsoDate($value, $minDate)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }

        // look for Ymd keys?
        if (is_array($value)) {
            $value = $this->_arrayToDate($value);
        }
        
        if ( strtotime($value) >= strtotime($minDate)) {
            return true;
        }

        return false;
    }
}
