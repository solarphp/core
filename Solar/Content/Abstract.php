<?php
/**
 * 
 * Abstract content node master.
 * 
 * @category Solar
 * 
 * @package Solar_Content
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
 * Abstract content node master.
 * 
 * @category Solar
 * 
 * @package Solar_Content
 * 
 */
abstract class Solar_Content_Abstract extends Solar_Base {
    
    /**
     * 
     * User-defined configuaration values.
     * 
     * : \\content\\ : (dependency) A Solar_Content dependency object.
     * 
     * : \\area_id\\ : (int) Only work with this area_id (if any).
     * 
     * : \\paging\\ : (int) The number of rows per page when fetching pages.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'content' => 'content',
        'area_id' => null,
        'paging'  => 10,
    );
    
    /**
     * 
     * Solar_Content dependency.
     * 
     * @var Solar_Content
     * 
     */
    protected $_content;
    
    /**
     * 
     * The master node type.
     * 
     * @var string
     * 
     */
    protected $_type;
    
    /**
     * 
     * Array of columns needed for forms related to the master node type.
     * 
     * @var array
     * 
     */
    protected $_form;
    
    /**
     * 
     * The default area ID to fetch nodes from.
     * 
     * If empty, will fetch from all areas.
     * 
     * @var int
     * 
     */
    protected $_area_id;
    
    /**
     * 
     * What node types are acceptable as parts of this master node type?
     * 
     * @var array
     * 
     */
    protected $_parts;
    
    /**
     * 
     * When fetching, get this many rows per page.
     * 
     * @var string
     * 
     */
    protected $_paging = 10;
    
    /**
     * 
     * Constructor
     * 
     * @param array $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_content = Solar::dependency(
            'Solar_Content',
            $this->_config['content']
        );
        $this->_area_id = $this->_config['area_id'];
    }
    
    /**
     * 
     * Sets the area ID from which nodes will be fetched.
     * 
     * If set to an empty value, nodes will be fetched from all areas.
     * 
     * @param int $area_id int The area ID.
     * 
     * @return void
     * 
     */
    public function setAreaId($area_id)
    {
        $this->_area_id = $area_id;
    }
    
    /**
     * 
     * Gets the area ID from which nodes are being fetched.
     * 
     * @return int The area ID.
     * 
     */
    public function getAreaId()
    {
        return $this->_area_id;
    }
    
    /**
     * 
     * Sets the number of rows per page.
     * 
     * @param int $val The number of rows per page.
     * 
     * @return void
     * 
     */
    public function setPaging($val)
    {
        $this->_paging = $val;
    }
    
    /**
     * 
     * Gets the number of rows per page.
     * 
     * @return int The number of rows per page.
     * 
     */
    public function getPaging()
    {
        return $this->_paging;
    }
    
    /**
     * 
     * Fetch a list of nodes of the master node type.
     * 
     * @param string|array $tags Fetch nodes with all these tags; if
     * empty, ignores tags.
     * 
     * @param string|array $where A set of multiWhere() conditions to
     * determine which nodes are fetched.
     * 
     * @param string|array $order Order the returned rows in this
     * fashion.
     * 
     * @param int $page Which page-number of results to fetch.
     * 
     * @return array The list of nodes.
     * 
     */
    public function fetchAll($tags = null, $where = null, $order = null,
        $page = null)
    {
        $select = Solar::factory('Solar_Sql_Select');
        $select->paging = $this->_paging;
        $select->from($this->_content->nodes, '*');
        $select->multiWhere($this->_masterWhere());
        
        // join back to the area to get its name
        $select->join('areas', 'areas.id = nodes.area_id', "name AS area_name");
        
        // get part counts?
        if ($this->_parts) {
            // join each table and get a count
            foreach ($this->_parts as $part) {
                // we left-join so that an absences of a part-type does
                // not return 0 rows for the main type
                // 
                // LEFT JOIN nodes AS comment_nodes ON comment_nodes.parent_id = nodes.id
                $join = $part . '_nodes';
                $type = $select->quote($part);
                $count = $part . '_count';
                $select->leftJoin(
                    // this table
                    "nodes AS $join",
                    // on these conditions
                    "$join.parent_id = nodes.id AND $join.type = $type",
                    // with these columns
                    "COUNT($join.id) AS $count"
                );
            }
        }
        
        // looking for certain tags?
        if (! empty($tags)) {
            // force the tags to an array (for the IN(...) clause)
            $tags = $this->_content->tags->asArray($tags);
            
            // build and return the select statement
            $select->join($this->_content->tags, 'tags.node_id = nodes.id');
            $select->where('tags.name IN (?)', $tags);
            $select->having("COUNT(nodes.id) = ?", count($tags));
        }
        
        // if either tags or part, group by ID
        if ($this->_parts || ! empty($tags)) {
            $select->group('nodes.id');
        }
        
        // add the custom pieces
        $select->multiWhere($where);
        $select->order($order);
        $select->limitPage($page);
        
        // return all rows
        return $select->fetch('all');
    }
    
    /**
     * 
     * Fetch a total count and pages of master nodes in the content store.
     * 
     * @param string|array $tags Count master nodes with all these
     * tags; if empty, counts for all tags.
     * 
     * @param string|array $where A set of multiWhere() conditions to
     * determine which nodes are fetched.
     * 
     * @return array A array with keys 'count' (total number of 
     * bookmarks) and 'pages' (number of pages).
     * 
     */
    public function countPages($tags = null, $where = null)
    {
        $select = Solar::factory('Solar_Sql_Select');
        $select->paging = $this->_paging;
        
        // only get the ID
        $select->from($this->_content->nodes, 'id');
        
        // master conditions
        $select->multiWhere($this->_masterWhere());
        
        // user conditions
        $select->multiWhere($where);
        
        // if not using tags, it's real easy.  if using tags, it's going
        // to be ugly.
        if (! $tags) {
            
            // yay, not using tags!
            return $select->countPages();
            
        } else {
            
            // using tags. this is going to be a hog.
            // force the tags to an array (for the IN comparison)
            $tags = $this->_content->tags->asArray($tags);
            
            // build the select statement
            $select->join($this->_content->tags, 'tags.node_id = nodes.id');
            $select->where('tags.name IN (?)', $tags);
            $select->group('nodes.id');
            $select->having('COUNT(nodes.id) = ?', count($tags));
            
            // fetch all rows and count how many we got (fat, stupid, slow)
            $all = $select->fetch('all');
            $result = count($all);
            unset($all);
            
            // $result is the row-count; how many pages does it convert to?
            $pages = 0;
            if ($result > 0) {
                $pages = ceil($result / $this->_paging);
            }
            
            // done!
            return array(
                'count' => $result,
                'pages' => $pages
            );
            
        }
    }
    
    /**
     * 
     * Fetch one master node by ID.
     * 
     * @param int $id The master node ID.
     * 
     * @return array The master node data.
     * 
     */
    public function fetch($id)
    {
        $select = Solar::factory('Solar_Sql_Select');
        $select->from($this->_content->nodes, '*');
        
        // join back to the area to get its name
        $select->join('areas', 'areas.id = nodes.area_id', "name AS area_name");
        
        // get part counts?
        if ($this->_parts) {
            // join each table and get a count
            foreach ($this->_parts as $part) {
                // JOIN nodes AS comment_nodes ON comment_nodes.parent_id = nodes.id
                $join = $part . '_nodes';
                $type = $select->quote($part);
                $count = $part . '_count';
                
                $select->leftJoin(
                    // this table
                    "nodes AS $join",
                    // on these conditions
                    "$join.parent_id = nodes.id AND $join.type = $type",
                    // with these columns
                    "COUNT($join.id) AS $count"
                );
            }
            $select->group('nodes.id');
        }
        
        // add conditions
        $select->multiWhere($this->_masterWhere());
        $select->where('nodes.id = ?', $id);
        
        // get the row
        return $select->fetch('row');
    }
    
    /**
     * 
     * Fetch the parts of a parent node ID.
     * 
     * @param int $parent_id The parent node ID.
     * 
     * @param array $order Return in this order.
     * 
     * @return array A list of nodes that are children of
     * the $parent_id node.
     * 
     */
    public function fetchParts($parent_id, $order = null)
    {
        $select = Solar::factory('Solar_Sql_Select');
        $select->from($this->_content->nodes, '*');
        $select->join('areas', 'areas.id = nodes.area_id', "name AS area_name");
        $select->where('nodes.parent_id = ?', $parent_id);
        $select->order($order);
        return $select->fetch('all');
    }
    
    /**
     * 
     * Fetches a default blank node of this type.
     * 
     * @return array An array of default data for a master node.
     * 
     */
    public function fetchDefault()
    {
        $data = $this->_content->nodes->fetchDefault();
        $data['area_id'] = $this->_area_id;
        $data['type']    = $this->_type;
        return $data;
    }
    
    /**
     * 
     * Fetches a list of all tags on all master nodes of this type.
     * 
     * @param string|array $where A set of multiWhere() conditions to
     * determine which nodes are fetched.
     * 
     * @return array An array of tags.
     * 
     */
    public function fetchTags($where = null)
    {
        $select = Solar::factory('Solar_Sql_Select');
        $select->from(
            $this->_content->tags,
            array('name', 'COUNT(tags.id) AS pos')
        );
        
        // join to the nodes table
        $select->join('nodes', 'tags.node_id = nodes.id');
        
        // add master conditions
        $select->multiWhere($this->_masterWhere());
        
        // add user conditions
        $select->multiWhere($where);
        
        // group by tag name
        $select->group('name');
        
        // order and return
        $select->order('name');
        return $select->fetch('pairs');
    }
    
    /**
     * 
     * Inserts or updates a master node.
     * 
     * @param array $data The node data.
     * 
     * @return array The data as inserted or updated.
     * 
     */
    public function save($data)
    {
        if (empty($data['id'])) {
            return $this->insert($data);
        } else {
            return $this->update($data);
        }
    }
    
    /**
     * 
     * Insert a new master node and its tags.
     * 
     * @param array $data The node data.
     * 
     * @return array The inserted data.
     * 
     */
    public function insert($data)
    {
        // force the type
        $data['type'] = $this->_type;
        
        // force the area?
        if ($this->_area_id) {
            $data['area_id'] = $this->_area_id;
        }
        
        // attempt the insert
        $data = $this->_content->nodes->insert($data);
        
        // add the tags to the tag-search table
        $this->_content->tags->refresh($data['id'], $data['tags']);
        
        // return the new node data
        return $data;
    }
    
    /**
     * 
     * Update a master node and its tags.
     * 
     * @param array $data The node data.
     * 
     * @return array The updated data.
     * 
     */
    public function update($data)
    {
        // force the type
        $data['type'] = $this->_type;
        
        // force the area?
        if ($this->_area_id) {
            $data['area_id'] = $this->_area_id;
        }
        
        // update the only the one node
        $where = array(
            'nodes.id = ?' => $data['id'],
        );
        $data = $this->_content->nodes->update($data, $where);
        
        // refresh the tags
        if (array_key_exists('tags', $data)) {
            $this->_content->tags->refresh($data['id'], $data['tags']);
        }
        
        // done
        return $data;
    }
    
    /**
     * 
     * Delete a master node and its tags.
     * 
     * @param int $id The master node ID to delete.
     * 
     * @return void
     * 
     */
    public function delete($id)
    {
        // delete the node
        $where = $this->_masterWhere();
        $where['nodes.id = ?'] = $id;
        $this->_content->nodes->delete($where);
        
        // now delete the tags.
        $where = array(
            'tags.node_id = ?' => $id,
        );
        $this->_content->tags->delete($where);
    }
    
    /**
     * 
     * Generates a data-entry form for a master node.
     * 
     * @param int|array $data An existing node ID, or an array of data to
     * pre-populate into the form.  The array should have a key
     * 'bookmarks' with a sub-array using keys for 'uri', 'subj', 'summ',
     * 'tags', and 'pos'.  If empty, default values are pre-populated
     * into the form.
     * 
     * @return object A Solar_Form object.
     * 
     */
    public function form($data = null)
    {
        // the basic form object
        $form = Solar::factory('Solar_Form');
        
        // what data should we populate into the form?
        if (empty($data)) {
            $data = $this->fetchDefault();
        }
        
        // set the form element labels and descriptions
        $info = array();
        foreach ((array) $this->_form as $col) {
            $info[$col] = array(
                'label' => $this->locale('LABEL_' . strtoupper($col)),
                'descr' => $this->locale('DESCR_' . strtoupper($col)),
                'value' => $data[$col],
            );
        }
        
        // load from the nodes table column definitions into a form,
        // as part of an array named for the node type.
        $form->load(
            'Solar_Form_Load_Table',
            $this->_content->nodes,
            $info,
            $this->_type
        );
        
        // populate basic data into the form and return
        $form->populate($data);
        return $form;
    }
    
    /**
     * 
     * Builds a baseline multiWhere() clause for master nodes of this type.
     * 
     * @return array
     * 
     */
    protected function _masterWhere()
    {
        $where = array();
        
        // limit to one area?
        if ($this->_area_id) {
            $where['nodes.area_id = ?'] = $this->_area_id;
        }
        
        // limit to one type
        $where['nodes.type = ?'] = $this->_type;
        
        // limit to master nodes
        $where['nodes.parent_id = ?'] = 0;
        
        // done
        return $where;
    }
}
?>