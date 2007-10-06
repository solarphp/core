<?php
/**
 * 
 * Sanitizes a value to an ISO-8601 timestamp.
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
 * Sanitizes a value to an ISO-8601 timestamp.
 * 
 * @category Solar
 * 
 * @package Solar_Filter
 * 
 */
class Solar_Filter_SanitizeIsoTimestamp extends Solar_Filter_Abstract {
    
    /**
     * 
     * Forces the value to an ISO-8601 formatted timestamp using a space
     * separator ("yyyy-mm-dd hh:ii:ss") instead of a "T" separator.
     * 
     * @param string $value The value to be sanitized.  If an integer, it
     * is used as a Unix timestamp; otherwise, converted to a Unix
     * timestamp using [[php::strtotime() | ]].
     * 
     * @return string The sanitized value.
     * 
     */
    public function sanitizeIsoTimestamp($value)
    {
        // if the value is not required, and is blank, sanitize to null
        $null = ! $this->_filter->getRequire() &&
                $this->_filter->validateBlank($value);
                
        if ($null) {
            return null;
        }
        
        // normal sanitize
        $format = 'Y-m-d H:i:s';
        if (is_int($value)) {
            return date($format, $value);
        } else {
            return date($format, strtotime($value));
        }
    }
}