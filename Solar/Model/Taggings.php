<?php
/**
 * 
 * A model of content "nodes" (individual pieces of content).
 * 
 * @category Solar
 * 
 * @package Solar_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Mime.php 2826 2007-10-06 15:55:03Z pmjones $
 * 
 */

/**
 * 
 * A model of content "nodes" (individual pieces of content).
 * 
 * @category Solar
 * 
 * @package Solar_Model
 * 
 */
class Solar_Model_Taggings extends Solar_Model {
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
