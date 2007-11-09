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

/**
 * 
 * Example for testing a model of content "nodes".
 * 
 * @category Solar
 * 
 * @package Solar_Example
 * 
 */
class Solar_Example_Model_Nodes extends Solar_Sql_Model {
    
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
        
        $this->_belongsTo('area', array(
            'foreign_class' => 'areas',
            'foreign_key'   => 'area_id',
        ));
        
        $this->_belongsTo('user', array(
            'foreign_class' => 'users',
            'foreign_key'   => 'user_id',
        ));
        
        $this->_hasOne('meta', array(
            'foreign_class' => 'metas',
            'foreign_key'   => 'node_id',
        ));
        
        $this->_hasMany('taggings', array(
            'foreign_class' => 'taggings',
            'foreign_key'   => 'node_id',
        ));
        
        $this->_hasMany('tags', array(
            'foreign_class' => 'tags',
            'through'       => 'taggings',
            'through_key'   => 'tag_id',
        ));
        
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