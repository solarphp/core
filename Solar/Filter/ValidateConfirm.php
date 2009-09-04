<?php
/**
 * 
 * Validates that the "confirmation" value is the same as the "real"
 * value being confirmed.
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
class Solar_Filter_ValidateConfirm extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the "confirmation" value is the same as the "real"
     * value being confirmed.
     * 
     * Useful for checking that the user entered the same password twice, or
     * the same email twice, etc.
     * 
     * If the $confirm_key does not exist in the data, the validation will 
     * *pass*.  (Compare this to validateCompare, where the same condition
     * means the validation will fail.)
     * 
     * @param mixed $value The value to validate.
     * 
     * @param string $confirm_key Check against the value of this element in
     * $this->_data. When empty, defaults to the current data col being 
     * processed, with suffix '_confirm'.
     * 
     * @return bool True if the values are the same or if the $confirm_key
     * is not in the data being processed. False if the values are not the
     * same.
     * 
     */
    public function validateConfirm($value, $confirm_key = null)
    {
        if (! $confirm_key) {
            $confirm_key = $this->_filter->getDataKey() . '_confirm';
        }
        
        $confirm_val = $this->_filter->getData($confirm_key);
        
        if ($confirm_val === null) {
            return true;
        } else {
            return ($value == $confirm_val);
        }
    }
}
