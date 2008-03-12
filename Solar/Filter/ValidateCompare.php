<?php
/**
 * 
 * Validates that this value is the same as some other value in the
 * data filter chain.
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
 * Validates that this value is the same as some other value in the
 * data filter chain.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 */
class Solar_Filter_ValidateCompare extends Solar_Filter_Abstract {
    
    /**
     * 
     * Validates that this value is the same as some other value in the
     * data filter chain.
     * 
     * When this value is exactly null, the comparison is not performed.
     * Empty string, numeric zero, and boolean false will all enable the
     * comparison check.
     * 
     * Useful for checking that the user entered the same password twice, or
     * the same email twice, etc.
     * 
     * Be sure to use this only as part of a filter chain, as it will attempt
     * to look up the other value in the filter data.
     * 
     * 
     * E.g., `validateCompare('email_confirm', 'email')`.  This means that
     * when `email_confirm` is exactly null, it will validate.
     * 
     * @param mixed $value The value to validate.  If exactly null, the 
     * validation will automatically pass.
     * 
     * @param string $compare_key Check against the value of this element in
     * $this->_data.
     * 
     * @param bool $strict When true, does a type comparison in addition to
     * a value comparison (i.e., `===` and not just `==`).
     * 
     * @return bool True if the values are the same, false if not.
     * 
     */
    public function validateCompare($value, $compare_key, $strict = false)
    {
        if ($value === null) {
            return true;
        }
        
        $compare_val = $this->_filter->getData($compare_key);
    
        if ($strict) {
            return $value === $compare_val;
        } else {
            return $value == $compare_val;
        }
    }
}
