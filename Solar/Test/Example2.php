<?php
/**
 * 
 * Example for testing Solar class-to-file hierarchy, locales, and exceptions.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id:$
 * 
 */

/**
 * 
 * Example for testing Solar class-to-file hierarchy, locales, and exceptions.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 */
class Solar_Test_Example2 extends Solar_Base {
    public function solarGenericException()
    {
        throw $this->_exception('ERR_CUSTOM_CONDITION');
    }
}
?>