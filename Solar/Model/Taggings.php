<?php
/**
 * 
 * A model of content "taggings" (maps tags to nodes).
 * 
 * @category Solar
 * 
 * @package Solar_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Model_Taggings extends Solar_Model
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
        $this->_table_name = 'taggings';
        
        $this->_table_cols = array(
            'id' => array(
                'type'    => 'int',
                'require' => true,
                'primary' => true,
                'autoinc' => true,
            ),
            'node_id' => 'int',
            'tag_id'  => 'int',
        );
        
        $this->_belongsTo('node', array(
            'foreign_class' => 'nodes',
            'foreign_key'   => 'node_id',
        ));
        
        $this->_belongsTo('tag', array(
            'foreign_class' => 'tags',
            'foreign_key'   => 'tag_id',
        ));
    }
}
