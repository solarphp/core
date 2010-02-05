<?php
/**
 * 
 * Example for testing a model of content "nodes".
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
class Mock_Solar_Model_Nodes extends Solar_Sql_Model
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
        
        $this->_belongsTo('area_false', array(
            'foreign_name' => 'areas',
            'where' => '0=1',
        ));
        
        $this->_belongsTo('user');
        
        $this->_hasOne('meta');
        
        $this->_hasOne('meta_false', array(
            'foreign_name' => 'metas',
            'where' => '0=1',
        ));
        
        $this->_hasMany('comments');
        
        $this->_hasMany('comments_false', array(
            'foreign_name' => 'comments',
            'where' => '0=1',
            'join_flag' => true, // force the eager join
        ));
        
        $this->_hasMany('taggings');
        
        $this->_hasManyThrough('tags', 'taggings');
        
        $this->_index_info = array(
            'created',
            'updated',
            'area_id',
            'user_id',
            'node_id',
            'inherit',
        );
        
        $this->_hasManyThrough('tags_false', 'taggings', array(
            'foreign_name' => 'tags',
            'where' => '0=1',
        ));
        
        $this->_hasMany('taggings_false', array(
            'foreign_name' => 'taggings',
            'where' => '0=1',
        ));
        
        $this->_hasManyThrough('tags_through_false', 'taggings_false', array(
            'foreign_name' => 'tags',
        ));
        
        $this->_hasManyThrough('tags_false_through_false', 'taggings_false', array(
            'foreign_name' => 'tags',
            'where' => '0=1',
        ));
    }
}