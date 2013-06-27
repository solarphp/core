<?php
/**
 * 
 * Validates that a value represents an integer.
 *
 * Note: In PHP an integer is a signed whole number with values between
 * -2147483648 an +2147483647 (usually, but this is platform dependent).
 * If you want to store a number that doesn't fit into this limits you
 * will run into issues with validateInt(). Take, for example, the number
 * 3581297294. This is a valid MySQL INT UNSIGNED value but is not an
 * integer in PHP and therefore validateInt() will return false. You have
 * to consider this if you want to store numbers that don't fit in a PHP
 * integer (MySQL: INT UNSIGNED and BIGINT, other databases have similar
 * types).
 *
 * @link http://us.php.net/manual/en/language.types.integer.php Info about Integers in PHP
 * @link http://dev.mysql.com/doc/refman/5.0/en/numeric-types.html Info about Numeric Types in MySQL
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
class Solar_Filter_ValidateInt extends Solar_Filter_Abstract
{
    /**
     * 
     * Validates that the value represents an integer.
     * 
     * @param mixed $value The value to validate.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    public function validateInt($value)
    {
        if ($this->_filter->validateBlank($value)) {
            return ! $this->_filter->getRequire();
        }
        
        if (is_int($value)) {
            return true;
        }
        
        // otherwise, must be numeric, and must be same as when cast to int
        return is_numeric($value) && $value == (int) $value;
    }
}