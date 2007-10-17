<?php
/**
 * 
 * Example for testing a "describe-table" model.
 * 
 * @category Solar
 * 
 * @package Solar_Example
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Exception.php 2804 2007-10-06 14:01:27Z pmjones $
 * 
 */

/**
 * 
 * Example for testing a "describe-table" model.
 * 
 * @category Solar
 * 
 * @package Solar_Example
 * 
 */
class Solar_Example_Model_TestSolarSqlDescribe extends Solar_Sql_Model {
    
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
    }
}