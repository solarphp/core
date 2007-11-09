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
 * @version $Id$
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
class Solar_Model_Nodes extends Solar_Model {
    
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
        
        $this->_table_name = 'nodes';
        
        $this->_table_cols = array(
            'id' => array(
                'type'    => 'int',
                'require' => true,
                'primary' => true,
                'autoinc' => true,
            ),
            'created' => 'timestamp',
            'updated' => 'timestamp',
            'area_id' => array(
                'type'    => 'int',
                'require' => true,
            ),
            'inherit' => array(
                'type'    => 'varchar',
                'size'    => 32,
            ),
            'name' => array(
                'type'    => 'varchar',
                'size'    => 127,
            ),
            'parent_id' => 'int',
            'owner_handle' => array(
                'type'    => 'varchar',
                'size'    => 255,
            ),
            'editor_handle' => array(
                'type'    => 'varchar',
                'size'    => 255,
            ),
            'editor_ipaddr' => array(
                'type'    => 'varchar',
                'size'    => 15,
            ),
            'assign_handle' => array(
                'type'    => 'varchar',
                'size'    => 255,
            ),
            'locale' => array(
                'type'    => 'varchar',
                'size'    => 5,
            ),
            'rating' => 'int',
            'email' => array(
                'type'    => 'varchar',
                'size'    => 255,
            ),
            'uri' => array(
                'type'    => 'varchar',
                'size'    => 255,
            ),
            'moniker' => array(
                'type'    => 'varchar',
                'size'    => 255,
            ),
            'pos' => 'int',
            'status' => array(
                'type'    => 'varchar',
                'size'    => 32,
            ),
            'mime' => array(
                'type'    => 'varchar',
                'size'    => 64,
                'default' => 'text/plain',
            ),
            'subj' => array(
                'type'    => 'varchar',
                'size'    => 255,
            ),
            'summ' => 'clob',
            'body' => 'clob',
            'prefs' => 'clob',
        );
        
        $this->_index = array(
            'created',
            'updated',
            'area_id',
            'name',
            'parent_id',
            'owner_handle',
            'assign_handle',
            'inherit',
            'locale',
            'pos',
            'rating',
            'uri',
            'email',
            'status',
        );
        
        /**
         * Special columns
         */
        $this->_serialize_cols[] = 'prefs';
        $this->_calculate_cols[] = 'tags_as_string';
        
        /**
         * Filters
         */
        
        // make sure the name is unique for its area and model
        $where = array(
            'inherit = :inherit',
            'area_id = :area_id',
        );
        $this->_addFilter('name', 'validateUnique', $where);
        
        // other filters
        $this->_addFilter('email', 'validateEmail');
        $this->_addFilter('uri', 'validateUri');
        $this->_addFilter('editor_ipaddr', 'validateIpv4');
        $this->_addFilter('locale', 'validateLocaleCode');
        $this->_addFilter('mime', 'validateMimeType');
        $this->_addFilter('tags_as_string', 'validateSepWords');
        
        /**
         * Relationships.
         */
        $this->_belongsTo('area', array(
            'foreign_class' => 'areas',
            'foreign_key'   => 'area_id',
            'eager'         => false,
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
    }
    
    /**
     * 
     * Fetches a collection of nodes with certain tags.
     * 
     * @param array $tag_list Fetch only nodes with all of these tags.
     * 
     * @param array $params Added parameters for the SELECT.
     * 
     * @return Solar_Model_Nodes_Collection
     * 
     */
    public function fetchAllByTags($tag_list, $params = null)
    {
        // no tags? fetch all to pre-empt errors related to "IN()" not
        // having a list to work with.
        $tag_list = $this->_fixTagList($tag_list);
        if (! $tag_list) {
            return $this->fetchAll($params);
        }
        
        // fetch
        $select = $this->_newSelectByTags($tag_list, $params);
        return $this->_fetchAll($select, $params);
    }
    
    /**
     * 
     * Support method to "fix" tag-list arrays: no duplicates, no spaces, etc.
     * 
     * @param array $tag_list The list of tags to "fix".
     * 
     * @return array The fixed tag list.
     * 
     */
    protected function _fixTagList($tag_list)
    {
        // convert to array
        if (! is_array($tag_list)) {
            $tag_list = preg_split('/\s+/', trim((string) $tag_list));
        }
        
        // no duplicates allowed
        $tag_list = array_unique($tag_list);
        
        // if the string tag-list is empty, the preg-split leaves one empty
        // element in the array.
        if ($tag_list[0] == '') {
            $tag_list = array();
        }
        
        // done!
        return $tag_list;
    }
    
    /**
     * 
     * Support method to create a new selection tool based on tag lists.
     * 
     * @param array $tag_list The list of tags to select by.
     * 
     * @param array $params Added parameters for the SELECT.
     * 
     * @return Solar_Sql_Select
     * 
     */
    protected function _newSelectByTags($tag_list, $params)
    {
        // setup
        $params = $this->fixSelectParams($params);
        $select = $this->newSelect($params['eager']);
        
        // catalog entries for joining
        $taggings = $this->_related['taggings'];
        $tags     = $this->_related['tags'];
        
        // primary key on the nodes table as an alias; e.g., "nodes.id"
        $native_primary = "{$this->_model_name}.{$this->_primary_col}";
        
        // http://forge.mysql.com/wiki/TagSchema
        // build the select differently from other fetchAll() statements
        $select->distinct($params['distinct'])
               ->from("{$this->_table_name} AS {$this->_model_name}", $params['cols'])
               // join taggings on nodes
               ->join(
                   "{$taggings->foreign_table} AS {$taggings->foreign_alias}",
                   "{$taggings->foreign_alias}.node_id = $native_primary"
               )
               // join tags on taggings
               ->join(
                   "{$tags->foreign_table} AS {$tags->foreign_alias}",
                   "{$tags->foreign_alias}.id = {$taggings->foreign_alias}.tag_id"
               )
               // select for the listed tags
               ->where("{$tags->foreign_alias}.name IN (?)", $tag_list)
               // user-provided WHERE
               ->multiWhere($params['where'])
               // group by nodes.id to collapse multiple nodes (1 for each tag)
               ->group($native_primary)
               // make sure the tag-count matches
               ->having("COUNT($native_primary) = ?", count($tag_list))
               // user-provided ORDER, paging, etc
               ->order($params['order'])
               ->setPaging($params['paging'])
               ->limitPage($params['page'])
               ->bind($params['bind']);
        
        // done!
        return $select;
    }
    
    /**
     * 
     * Gets a count of nodes and pages-of-nodes with certain tags.
     * 
     * @param array $tag_list Count only nodes with all of these tags.
     * 
     * @param array $params Added parameters for the SELECT.
     * 
     * @return array An array with elemets 'count' (the number of nodes) and
     * 'pages' (the number of pages-of-nodes).
     * 
     */
    public function countPagesByTags($tag_list, $params = null)
    {
        $tag_list = $this->_fixTagList($tag_list);
        if (! $tag_list) {
            return $this->countPages($params);
        }
        
        // we need to select the nodes + tags as an "inner" sub-select;
        // clear any limits on it.
        $inner = $this->_newSelectByTags($tag_list, $params);
        $inner->clear('limit');
        
        // set up the outer select, which will wrap the inner sub-select
        $outer = Solar::factory($this->_select_class, array(
            'sql' => $this->_sql
        ));
        
        // wrap the sub-select and make sure paging is correct
        $outer->fromSelect($inner, $this->_model_name);
        $outer->setPaging($this->_paging);
        
        // *now* get the count of pages with the tags requested
        return $outer->countPages("{$this->_model_name}.{$this->_primary_col}");
    }
}
