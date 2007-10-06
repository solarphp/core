<?php
class Solar_Example_Model_Tags extends Solar_Sql_Model {
    
    protected function _setup()
    {
        $dir = str_replace('_', DIRECTORY_SEPARATOR, __CLASS__)
             . DIRECTORY_SEPARATOR
             . 'Setup'
             . DIRECTORY_SEPARATOR;
        
        $this->_table_name = Solar::run($dir . 'table_name.php');
        $this->_table_cols = Solar::run($dir . 'table_cols.php');
        
        $this->_hasMany('taggings');
        $this->_hasMany('nodes', array(
            'through' => 'taggings',
        ));
        
        $this->_index = array(
            'name' => 'unique',
        );
    }
}