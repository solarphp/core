<?php
/**
 * 
 * Example for testing Solar class-to-file hierarchy, locales, and exceptions.
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Example for testing Solar class-to-file hierarchy, locales, and exceptions.
 * 
 * @category Solar
 * 
 * @package Solar_Test
 * 
 */
class Solar_Test_Example extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'foo' => 'bar',
        'baz' => 'dib',
        'zim' => 'gir',
    );
    
    /**
     * 
     * Throws ERR_CUSTOM_CONDITION for this class.
     * 
     * @return void
     * 
     */
    public function classSpecificException()
    {
        throw $this->_exception('ERR_CUSTOM_CONDITION');
    }
    
    /**
     * 
     * Throws ERR_FILE_NOT_FOUND for this class.
     * 
     * @return void
     * 
     */
    public function solarSpecificException()
    {
        throw $this->_exception('ERR_FILE_NOT_FOUND');
    }
    
    /**
     * 
     * Throws ERR_GENERIC_CONDITION for this class.
     * 
     * @return void
     * 
     */
    public function classGenericException()
    {
        throw $this->_exception('ERR_GENERIC_CONDITION');
    }
    
    /**
     * 
     * Throws ERR_NO_SUCH_CONDITION for this class.
     * 
     * @return void
     * 
     */
    public function solarGenericException()
    {
        throw $this->_exception('ERR_NO_SUCH_CONDITION');
    }
    
    /**
     * 
     * Throws a user-specified error code for this class.
     * 
     * @param string $code The error code to throw.
     * 
     * @return void
     * 
     */
    public function exceptionFromCode($code) {
        throw $this->_exception($code);
    }
}
?>