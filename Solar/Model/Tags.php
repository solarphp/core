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
 * @version $Id: Mime.php 2826 2007-10-06 15:55:03Z pmjones $
 * 
 */

/**
 * 
 * A model of "tags" that can be applied to nodes.
 * 
 * @category Solar
 * 
 * @package Solar_Model
 * 
 */
class Solar_Model_Tags extends Solar_Model {
    
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
        $this->_hasMany('taggings', array(
            'foreign_class' => 'taggings',
            'foreign_key'   => 'tag_id',
        ));
        
        $this->_hasMany('nodes', array(
            'foreign_class' => 'nodes',
            'through'       => 'taggings',
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
        $params = $this->fixSelectParams($params);
        $select = $this->_newSelectWithCount($params);
        return $this->_fetchAll($select, $params);
    }
    
    /**
     * 
     * Fetches a collection of all tags applied by a particular owner, as
     * identified by that user's "handle".
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
        $owner_handle = trim($owner_handle);
        if (! $owner_handle) {
            return $this->fetchAll($params);
        }
        
        // setup
        $params = $this->fixSelectParams($params);
        $select = $this->newSelect($params['eager']);
        
        // catalog entries for joining
        $taggings = $this->getRelated('taggings');
        $nodes    = $this->getRelated('nodes');
        
        // primary key on this table alias; e.g., tags.id
        $native_primary = "{$this->_model_name}.{$this->_primary_col}";
        
        // add a tag-count column
        $params['cols'][] = "COUNT($native_primary) AS count";
        
        // build the select
        $select->distinct($params['distinct'])
               ->from("{$this->_table_name} AS {$this->_model_name}", $params['cols'])
               // join taggings on tags
               ->join(
                   "{$taggings->foreign_table} AS {$taggings->foreign_alias}",
                   "{$taggings->foreign_alias}.tag_id = $native_primary"
               )
               // join nodes on taggings
               ->join(
                   "{$nodes->foreign_table} AS {$nodes->foreign_alias}",
                   "{$nodes->foreign_alias}.id = {$taggings->foreign_alias}.node_id"
               )
               // select for the owner_handle
               ->where("{$nodes->foreign_alias}.owner_handle = ?", $owner_handle)
               // group on primary key for counts
               ->group($native_primary)
               // user-provided ORDER, paging, etc
               ->multiWhere($params['where'])
               ->order($params['order'])
               ->setPaging($params['paging'])
               ->limitPage($params['page'])
               ->bind($params['bind']);
        
        // fetch
        $select = $this->_newSelectWithCount($params);
        $select->where("{$nodes->foreign_alias}.owner_handle = ?", $owner_handle);
        return $this->_fetchAll($select, $params);
    }
    
    /**
     * 
     * Support method to add the tag-count to a selection tool.
     * 
     * @param array $params Added parameters for the select.
     * 
     * @return Solar_Sql_Select
     * 
     */
    protected function _newSelectWithCount($params)
    {
        // params should have been fixed by this point
        $select = $this->newSelect($params['eager']);
        
        // catalog entries for joining
        $taggings = $this->getRelated('taggings');
        $nodes    = $this->getRelated('nodes');
        
        // primary key on this table alias; e.g., tags.id
        $native_primary = "{$this->_model_name}.{$this->_primary_col}";
        
        // add a tag-count column
        $params['cols'][] = "COUNT($native_primary) AS count";
        
        // build the select
        $select->distinct($params['distinct'])
               ->from("{$this->_table_name} AS {$this->_model_name}", $params['cols'])
               // join taggings on tags
               ->join(
                   "{$taggings->foreign_table} AS {$taggings->foreign_alias}",
                   "{$taggings->foreign_alias}.tag_id = $native_primary"
               )
               // join nodes on taggings
               ->join(
                   "{$nodes->foreign_table} AS {$nodes->foreign_alias}",
                   "{$nodes->foreign_alias}.id = {$taggings->foreign_alias}.node_id"
               )
               // group on primary key for counts
               ->group($native_primary)
               // user-provided ORDER, paging, etc
               ->multiWhere($params['where'])
               ->order($params['order'])
               ->setPaging($params['paging'])
               ->limitPage($params['page'])
               ->bind($params['bind']);
        
        return $select;
    }
}
