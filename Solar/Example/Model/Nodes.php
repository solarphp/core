<?php
/**
 * 
 * Example for testing a model of content "nodes".
 * 
 * @category Solar
 * 
 * @package Solar_Example
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Example_Model_Nodes extends Solar_Sql_Model
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
        
        $this->_model_name = 'nodes';
        
        $this->_belongsTo('area');
        
        $this->_belongsTo('user');
        
        $this->_hasOne('meta');
        
        $this->_hasMany('comments');
        
        $this->_hasMany('taggings');
        
        $this->_hasManyThrough('tags', 'taggings');
        
        $this->_index = array(
            'created',
            'updated',
            'area_id',
            'user_id',
            'node_id',
            'inherit',
        );
    }
}