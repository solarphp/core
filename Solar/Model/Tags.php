<?php
/**
 * 
 * Tags on nodes.
 * 
 * @category Solar
 * 
 * @package Solar_Model
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * Solar_Sql_Table
 */
Solar::loadClass('Solar_Sql_Table');

/**
 * 
 * Tags on nodes.
 * 
 * @category Solar
 * 
 * @package Solar_Model
 * 
 */
class Solar_Model_Tags extends Solar_Sql_Table {
    
    /**
     * 
     * Normalizes tag strings.
     * 
     * Converts "+" to " ", trims extra spaces, and removes duplicates,
     * but otherwise keeps them in order and space-separated.
     * 
     * Also converts arrays to a normalized tag string.
     * 
     * @param string|array $tags A space-separated string of tags, or a
     * sequential array of tags.
     * 
     * @return string A space-separated string of tags.
     * 
     */
    public function asString($tags)
    {
        // convert to array from string?
        if (! is_array($tags)) {
            
            // convert all "+" to spaces (this is for URL values)
            $tags = str_replace('+', ' ', $tags);
            
            // trim all surrounding spaces and extra spaces
            $tags = trim($tags);
            $tags = preg_replace('/[ ]{2,}/', ' ', $tags);
            
            // convert to array for easy processing
            $tmp = explode(' ', $tags);
        }
        
        // make sure each tag is unique (no double-entries)
        $tags = array_unique($tags);
        
        // return as space-separated text
        return implode(' ', $tmp);
    }
    
    /**
     * 
     * Normalizes tag arrays.
     * 
     * Also converts strings to a normalized tag array.
     * 
     * @param string|array $tags A space-separated string of tags, or a
     * sequential array of tags.
     * 
     * @return string A space-separated string of tags.
     * 
     */
    public function asArray($tags)
    {
        // normalize to string...
        $tags = $this->asString($tags);
        // ... and convert to array
        return explode(' ', $tags);
    }
    
    /**
     * 
     * Fetch all tags on one node_id.
     * 
     * @param int $node_id The node_id to fetch tags for.
     * 
     * @return array An array of tags on that node.
     * 
     */
    public function fetchAllByNodeId($node_id)
    {
        $select = Solar::factory('Solar_Sql_Select');
        $select->from($this, 'name');
        $select->where('node_id = ?', $node_id);
        return $select->fetch('col');
    }
    
    /**
     * 
     * "Refreshes" the tags for a node_id by diff.
     * 
     * @param int $node_id The node_id to work with.
     * 
     * @param string|array $tags A space-separated string of tags, or a
     * sequential array of tags.  These are the replacement tags.
     * 
     * @return void
     * 
     * @todo Collect errors and return as needed.
     * 
     */
    public function refresh($node_id, $tags)
    {
        $node_id = (int) $node_id;
        
        // get the old set of tags
        $old = $this->fetchAllByNodeId($node_id);
        
        // normalize the new tags to an array
        $new = $this->asArray($tags);
        
        // diff the tagsets
        $diff = $this->_diff($old, $new);
        
        // delete
        if (! empty($diff['del'])) {
            $where = array(
                'node_id = ?' => $node_id,
                'name IN (?)' => $diff['del'],
            );
            $this->delete($where);
        };
        
        // insert
        foreach ($diff['ins'] as $name) {
            $data = array(
                'node_id' => $node_id,
                'name'    => $name
            );
            $this->insert($data);
        }
        
        // done!
    }
    
    /**
     * 
     * Determines the diff (delete/insert matrix) between two tag sets.
     * 
     * <code>
     * $old = array('a', 'b', 'c');
     * $new = array('c', 'd', 'e');
     * 
     * // perform the diff
     * $diff = $this->_diff($old, $new);
     * 
     * // the results are:
     * // $diff['del'] == array('a', 'b');
     * // $diff['ins'] == array('d', 'e');
     * // 'c' doesn't show up becuase it's present in both sets
     * </code>
     * 
     * @param array $old The old (previous) set of tags.
     * 
     * @param array $new The new (current) set of tags.
     * 
     * @return array An associative array of two keys: 'del' (where the
     * value is a sequential array of tags removed from the old set)
     * and 'ins' (where the value is a sequential array of tags to be
     * added from the new set).
     * 
     */
    protected function _diff($old, $new)
    {
        // find intersections first
        $intersect = array_intersect($old, $new);
        
        // now flip arrays so we can unset easily by key
        $old = array_flip($old);
        $new = array_flip($new);
        
        // remove intersections from each array
        foreach ($intersect as $val) {
            unset($old[$val]);
            unset($new[$val]);
        }
        
        // keys remaining in $old are to be deleted,
        // keys remaining in $new are to be added
        return array(
            'del' => (array) array_keys($old),
            'ins' => (array) array_keys($new)
        );
    }
    
    /**
     * 
     * Schema setup.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
        // the table name
        $this->_name = 'tags';
        
        // -------------------------------------------------------------
        // 
        // COLUMNS
        // 
        
        // the node_id this tag came from
        $this->_col['node_id'] = array(
            'type'    => 'int',
            'require' => true,
        );
        
        // the tag itself
        $this->_col['name'] = array(
            'type'    => 'varchar',
            'size'    => 127,
            'require' => true,
            'valid'   => 'word',
        );
        
        
        // -------------------------------------------------------------
        // 
        // KEYS AND INDEXES
        // 
        
        $this->_idx = array(
            'node_id' => 'normal',
            'name'    => 'normal',
        );
    }
}
?>