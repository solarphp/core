<?php
class Solar_Example_Model_TestSolarSpecialCols extends Solar_Sql_Model {
    
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
        
        $this->_table_name = Solar::run($dir . 'table_name.php');
        $this->_table_cols = Solar::run($dir . 'table_cols.php');
        
        // recognize sequence columns
        $this->_sequence_cols = array(
            'seq_foo' => 'test_solar_foo',
            'seq_bar' => 'test_solar_bar',
        );
        
        // recognize serialize columns
        $this->_serialize_cols = 'serialize';
    }
}