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
     * The node type this class is intended to work with.
     * 
     * @var string
     * 
     */
    protected $_type;
    
    /**
     * 
     * Array of columns needed for forms related to the node type.
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
     * What node types are acceptable as parts of this node type?
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
     * With fetchAll(), use this as the default order.
     * 
     * @var string
     * 
     * @see Solar_Content_Abstract::fetchAll()
     * 
     */
    protected $_order = null;
    
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
     * Fetch a list of nodes of the node type.
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
     * @return Solar_Sql_Rowset
     * 
     */
    public function fetchAll($tags = null, $where = null, $order = null,
        $page = null)
    {
        // set the default order if needed
        if (! $order) {
            $order = $this->_order;
        }
        
        if (! empty($tags)) {
            // force the tags to an array (for the IN(...) clause)
            $tags = $this->_content->tags->asArray($tags);
        }
        
        // getting just tags, or just part-counts, is fine as a normal select.
        // but getting tagged part-counts requires a sub-select.
        if ($tags && $this->_parts) {
            
            // create the tags inner select
            $subselect = Solar::factory('Solar_Sql_Select');
            
            $subselect->from($this->_content->nodes, '*')
                      ->multiWhere($this->_where())
                      ->multiWhere($where);
                      
            $this->_selectTags($subselect, $tags);
            
            // wrap in a part-count outer select
            $select = Solar::factory('Solar_Sql_Select');
            $select->fromSelect($subselect, 'nodes');
            $this->_selectPartCounts($select, $this->_parts);
            
        } else {
            
            $select = Solar::factory('Solar_Sql_Select');
            
            $select->from($this->_content->nodes, '*')
                   ->multiWhere($this->_where())
                   ->multiWhere($where);
            
            if ($tags) {
                $this->_selectTags($select, $tags);
            } elseif ($this->_parts) {
                $this->_selectPartCounts($select, $this->_parts);
            }
            
        }
        
        $select->setPaging($this->_paging);
        $select->order($order);
        $select->limitPage($page);
        
        $all = $select->fetch('all');
        $all->setSave($this);
        return $all;
    }
    
    /**
     * 
     * Given an existing select object, add part-count selection to it.
     * 
     * Note that this acts on the object reference directly.
     * 
     * @param Solar_Sql_Select $select The select object.
     * 
     * @param array $parts The parts to get counts for.
     * 
     * @return void
     * 
     */
    protected function _selectPartCounts($select, $parts)
    {
        // join each table and get a count
        foreach ($parts as $part) {
            // we left-join so that an absences of a part-type does
            // not return 0 rows for the main type
            // 
            // LEFT JOIN nodes AS comment_parts ON comment_parts.parent_id = nodes.id
            $join = $part . '_parts';
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
    
    /**
     * 
     * Given an existing select object, add tag-based selection to it.
     * 
     * Note that this acts on the object reference directly.
     * 
     * @param Solar_Sql_Select $select The select object.
     * 
     * @param array $tags Select nodes with these tags.
     * 
     * @return void
     * 
     */
    protected function _selectTags($select, $tags)
    {
        $select->join($this->_content->tags, 'tags.node_id = nodes.id')
               ->where('tags.name IN (?)', $tags)
               ->having("COUNT(nodes.id) = ?", count($tags))
               ->group("nodes.id");
    }
    
    /**
     * 
     * Fetch a total count and pages of nodes in the content store.
     * 
     * @param string|array $tags Count nodes with all these
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
        $select->from($this->_content->nodes, 'id');
        $select->multiWhere($this->_where());
        $select->multiWhere($where);
        
        // using tags?
        $tags = $this->_content->tags->asArray($tags);
        if ($tags) {
            // add tags to the query
            $this->_selectTags($select, $tags);
            // wrap as a sub-select
            $wrap = Solar::factory('Solar_Sql_Select');
            $wrap->fromSelect($select, 'nodes');
            $wrap->setPaging($this->_paging);
            return $wrap->countPages('nodes.id');
        } else {
            // no need for subselect
            return $select->countPages('nodes.id');
        }
    }
    
    /**
     * 
     * Fetch one node by ID.
     * 
     * @param int $id The node ID.
     * 
     * @return Solar_Sql_Row
     * 
     */
    public function fetch($id)
    {
        $select = Solar::factory('Solar_Sql_Select');
        $select->from($this->_content->nodes, '*');
        
        // get part counts?
        if ($this->_parts) {
            $this->_selectPartCounts($select, $this->_parts);
        }
        
        // add conditions
        $select->multiWhere($this->_where());
        $select->where('nodes.id = ?', $id);
        
        // get the row
        $row = $select->fetch('row');
        $row->setSave($this);
        return $row;
    }
    
    /**
     * 
     * Fetch the parts of a parent node ID.
     * 
     * @param int $parent_id The parent node ID.
     * 
     * @param string $where Additional WHERE conditions.
     * 
     * @param array $order Return in this order.
     * 
     * @return Solar_Sql_Rowset A list of nodes that are children of
     * the $parent_id node.  The parts are not save()-able.
     * 
     */
    public function fetchParts($parent_id, $where = null, $order = null)
    {
        $select = Solar::factory('Solar_Sql_Select');
        $select->from($this->_content->nodes, '*');
        $select->where('nodes.parent_id = ?', $parent_id);
        $select->multiWhere($where);
        $select->order($order);
        return $select->fetch('all');
    }
    
    /**
     * 
     * Fetches a default blank node of this type.
     * 
     * @return Solar_Sql_Row A default row for this type.
     * 
     */
    public function fetchNew()
    {
        $row = $this->_content->nodes->fetchNew();
        $row->area_id = $this->_area_id;
        $row->type    = $this->_type;
        $row->setSave($this);
        return $row;
    }
    
    /**
     * 
     * Fetches a list of all tags on all nodes of this type.
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
        $select->multiWhere($this->_where());
        
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
     * Inserts or updates a node.
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
     * Insert a new node and its tags.
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
        
        // force the IP address
        $data['editor_ipaddr'] = Solar::server('REMOTE_ADDR');
        
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
     * Update a node and its tags.
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
        
        // force the IP address
        $data['editor_ipaddr'] = Solar::server('REMOTE_ADDR');
        
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
     * Delete a node and its tags.
     * 
     * @param int $id The node ID to delete.
     * 
     * @return void
     * 
     */
    public function delete($id)
    {
        // delete the node
        $where = $this->_where();
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
     * Generates a data-entry form for a node.
     * 
     * @param array $data An array of "column => value" data to
     * pre-populate into the form.
     * 
     * @return Solar_Form
     * 
     */
    public function form($data = null)
    {
        // the basic form object
        $form = Solar::factory('Solar_Form');
        
        // what data should we populate into the form?
        if (empty($data)) {
            $data = $this->fetchNew();
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
     * Returns a baseline WHERE clause for nodes of $this->_type.
     * 
     * @return array
     * 
     */
    protected function _where()
    {
        $where = array();
        
        // limit to one area?
        if ($this->_area_id) {
            $where['nodes.area_id = ?'] = $this->_area_id;
        }
        
        // limit to one type
        $where['nodes.type = ?'] = $this->_type;
        
        // done
        return $where;
    }
}
?>