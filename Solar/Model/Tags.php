<?php
/**
 * 
 * A model of "tags" that can be applied to nodes.
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
class Solar_Model_Tags extends Solar_Model
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
        /**
         * Table name, columns, and indexes.
         */
        $this->_table_name = 'tags';
        
        $this->_table_cols = array(
            'id' => array(
                'type'    => 'int',
                'require' => true,
                'primary' => true,
                'autoinc' => true,
            ),
            'name' => array(
                'type'    => 'varchar',
                'size'    => 255,
                'require' => true,
            ),
            'descr' => array(
                'type'    => 'varchar',
                'size'    => 255,
            ),
        );
        
        $this->_index = array(
            'name' => 'unique',
        );
        
        /**
         * Behaviors (serialize, sequence, filter).
         */
        $this->_serialize_cols[] = 'prefs';
        
        /**
         * Relationships.
         */
        $this->_hasMany('taggings');
        
        $this->_hasMany('nodes', array(
            'through' => 'taggings',
        ));
    }
    
    /**
     * 
     * Fetches a collection of all tags, with an added "count" column saying
     * how many nodes use that tag.
     * 
     * @param array $params Added paramters for the select.
     * 
     * @return Solar_Model_Tags_Collection
     * 
     */
    public function fetchAllWithCount($params = null)
    {
        // primary key on this table alias; e.g., tags.id
        $native_primary = "{$this->_model_name}.{$this->_primary_col}";
        
        // fetch all columns, plus a count column
        $params['cols'] = $this->_fetch_cols;
        $params['cols'][] = "COUNT($native_primary) AS count";
        
        // group on primary key for counts
        $params['group'][] = $native_primary;
        
        // eager-join to nodes for the count of nodes.
        // force the join even though we're not fetching nodes, so  that
        // the counts come back.
        $params['eager']['nodes']['require_related'] = true;
        
        // done with params
        return $this->fetchAll($params);
    }
    
    /**
     * 
     * Fetches a collection of all tags applied by a particular owner (as
     * identified by that user's "handle") with the count of nodes using each
     * tag.
     * 
     * @param string $owner_handle Only select tags in use by this handle.
     * 
     * @param array $params Added parameters for the select.
     * 
     * @return Solar_Model_Tags_Collection
     * 
     */
    public function fetchAllByOwnerHandle($owner_handle, $params = null)
    {
        $params['where']['nodes.owner_handle = ?'] = $owner_handle;
        return $this->fetchAllWithCount($params);
    }
}
