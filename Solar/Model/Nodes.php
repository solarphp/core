<?php
/**
 * 
 * A model for nodes of content within an area.
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
class Solar_Model_Nodes extends Solar_Sql_Model {
    
    /**
     * 
     * Model-specific setup.
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
        $this->_index      = Solar_File::load($dir . 'index_info.php');
        
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
        $this->_belongsTo('area');
        
        $this->_hasMany('taggings');
        
        $this->_hasMany('tags', array(
            'through' => 'taggings',
        ));
    }
    
    /**
     * 
     * Fetches a collection of nodes with certain tags.
     * 
     * @param array $tag_list Fetch only nodes with all of these tags. If
     * empty, will fetch all nodes.
     * 
     * @param array $params Added parameters for the SELECT.
     * 
     * @return Solar_Model_Nodes_Collection
     * 
     */
    public function fetchAllByTags($tag_list, $params = null)
    {
        $params = $this->_fixFetchParams($params);
        $this->_modParamsJoinTags($params, $tag_list);
        return $this->fetchAll($params);
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
        
        // if the string tag-list was empty, the preg-split leaves one empty
        // element in the array.
        if (count($tag_list) == 1 && reset($tag_list) == '') {
            $tag_list = array();
        }
        
        // done!
        return $tag_list;
    }
    
    /**
     * 
     * Modifies a params object **in place** to add joins for a tag list.
     * 
     * The methodology is taken and modified from
     * <http://forge.mysql.com/wiki/TagSchema#Items_Having_All_of_A_Set_of_Tags>.
     * 
     * @param Solar_Sql_Model_Params_Fetch $params The fetch params.
     * 
     * @param array $tags A list of unique tags.
     * 
     * @return void
     * 
     */
    protected function _modParamsJoinTags(Solar_Sql_Model_Params_Fetch $params, $tags)
    {
        // normalize the tag list
        $tags = $this->_fixTagList($tags);
        
        // if no tags, no need to modify
        if (! $tags) {
            return;
        }
        
        // since this model uses single-table inheritance, we need the model
        // alias, not just the table name.
        $alias = $this->_model_name;
        
        // for each tag, add a join to tags and taggings, chaining each
        // subsequent join to the previous one.
        // the first tag join-pair is special; we connect it to the nodes
        // table directly, since we don't have a previous join to chain from.
        $params->join(array(
            'type' => "inner",
            'name' => "taggings AS taggings1",
            'cond' => "taggings1.node_id = {$alias}.id"
        ));
        
        $params->join(array(
            'type' => "inner",
            'name' => "tags AS tags1",
            'cond' => "taggings1.tag_id = tags1.id"
        ));
        
        // take the first tag off the top of the list
        $val = array_shift($tags);
        $params->where("tags1.name = ?", $val);
        
        // now deal with all remaining tags, chaining each current join to the
        // previous one.
        foreach ($tags as $key => $val) {
            $curr = $key + 2; // because keys are zero-based, and we already shifted one
            $prev = $key + 1;
            
            // the "through" table
            $params->join(array(
                'type' => "inner",
                'name' => "taggings AS taggings{$curr}",
                'cond' => "taggings{$curr}.node_id = taggings{$prev}.node_id"
            ));
            
            // the "tags" table
            $params->join(array(
                'type' => "inner",
                'name' => "tags AS tags{$curr}",
                'cond' => "taggings{$curr}.tag_id = tags{$curr}.id"
            ));
            
            // the WHERE condition for the tag name
            $params->where("tags{$curr}.name = ?", $val);
        }
    }
}
