<?php
/**
 * 
 * A model of content "areas" (logical groups or buckets of nodes).
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

/**
 * 
 * A model of content "areas" (logical groups or buckets of nodes).
 * 
 * @category Solar
 * 
 * @package Solar_Model
 * 
 */
class Solar_Model_Areas extends Solar_Model {
    
    /**
     * 
     * Model setup.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        /**
         * Table name, columns, and indexes.
         */
        $this->_table_name = 'areas';
        
        $this->_table_cols = array(
            'id' => array(
                'type'    => 'int',
                'require' => true,
                'primary' => true,
                'autoinc' => true,
            ),
            'created' => 'timestamp',
            'updated' => 'timestamp',
            'name' => array(
                'type'    => 'varchar',
                'size'    => 127,
                'require' => true,
            ),
            'owner_handle' => array(
                'type'    => 'varchar',
                'size'    => 32,
            ),
            'subj' => array(
                'type'    => 'varchar',
                'size'    => 255,
            ),
            'summ' => array(
                'type'    => 'varchar',
                'size'    => 255,
            ),
            'prefs' => 'clob',
        );
        
        $this->_index = array(
            'created',
            'updated',
            'name' => 'unique',
            'owner_handle',
        );
        
        /**
         * Behaviors (serialize, sequence, filter).
         */
        $this->_serialize_cols[] = 'prefs';
        
        /**
         * Relationships.
         */
        $this->_hasMany('nodes', array(
            'foreign_class' => 'nodes',
            'foreign_key'   => 'parent_id',
        ));
        
    }
}
