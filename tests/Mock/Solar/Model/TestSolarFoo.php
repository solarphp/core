<?php
/**
 * 
 * Example for testing a "test_solar_foo" model.
 * 
 * @category Solar
 * 
 * @package Mock_Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Mock_Solar_Model_TestSolarFoo extends Solar_Sql_Model
{
    /**
     * 
     * Model setup.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        $dir = str_replace('_', DIRECTORY_SEPARATOR, __CLASS__)
             . DIRECTORY_SEPARATOR
             . 'Setup'
             . DIRECTORY_SEPARATOR;
        
        $this->_table_name = Solar_File::load($dir . 'table_name.php');
        $this->_table_cols = Solar_File::load($dir . 'table_cols.php');
        
        $this->_addFilter('email', 'validateEmail');
        $this->_addFilter('uri', 'validateUri');
        
        $this->_index = array(
            'created',
            'updated',
            'email' => 'unique',
            'uri',
        );
    }
}