<?php
/**
 * 
 * Model class representing tags available to content nodes.
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
class Solar_Model_Tags extends Solar_Sql_Model {
    
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
        // fix up so we can manipulate easier, esp. to get the table alias
        $params = $this->_fixFetchParams($params);
        
        // count the number of nodes.
        $params->cols("COUNT(nodes.id) AS count");
        
        // group on primary key for counts
        $native_col = "{$params['alias']}.{$this->_primary_col}";
        $params->group($native_col);
        
        // eager-join to nodes for the count of nodes.
        // force the join even though we're not fetching nodes, so  that
        // the counts come back.
        $params->eager('nodes', array(
            'join_only' => true,
        ));
        
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
