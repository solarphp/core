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
class Solar_Test_Example extends Solar_Base {
    
    protected $_config = array(
        'foo' => 'bar',
        'baz' => 'dib',
        'zim' => 'gir',
    );
    
    public function classSpecificException()
    {
        throw $this->_exception('ERR_CUSTOM_CONDITION');
    }
    
    public function solarSpecificException()
    {
        throw $this->_exception('ERR_FILE_NOT_FOUND');
    }
    
    public function classGenericException()
    {
        throw $this->_exception('ERR_GENERIC_CONDITION');
    }
}
?>