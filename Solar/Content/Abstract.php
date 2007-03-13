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
     * `content`
     * : (dependency) A Solar_Content dependency object.
     * 
     * `area_id`
     * : (int) Only work with this area_id (if any).
     * 
     * `paging`
     * : (int) The number of rows per page when fetching pages.
     * 
     * @var array
     * 
     */
    protected $_Solar_Content_Abstract = array(
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
     * Fetch one node by ID.
     * 
     * @param int $id The node ID.
     * 
     * @return Solar_Sql_Row
     * 
     */
    public function fetch($id)
    {
        $where = array('nodes.id = ?' => (int) $id);
        return $this->fetchRow($where);
    }
    
    /**
     * 
     * Fetch one node by arbitrary WHERE clause.
     * 
     * @param string|array $where WHERE conditions.
     * 
     * @param string $order Optional ORDER clause.
     * 
     * @return Solar_Sql_Row
     * 
     */
    public function fetchRow($where, $order = null)
    {
        $select = Solar::factory('Solar_Sql_Select');
        $select->from($this->_content->nodes, '*');
        
        // join for area name
        $select->join(
            $this->_content->areas,
            'nodes.area_id = areas.id',
            'name AS area_name'
        );
        
        // add master and user conditions
        $select->multiWhere($this->_where());
        $select->multiWhere($where);
        
        // add order
        if ($order) {
            $select->order($order);
        }
        
        // get the row
        $row = $select->fetch('row');
        $row->setSave($this);
        
        // fetch and append the part counts.
        // check that the row ID exists so that we don't get SQL
        // errors looking for empty/nonexistent nodes.
        if ($this->_parts && $row->id) {
            $part_count = $this->_fetchPartCounts($row->id);
            foreach ($part_count as $val) {
                $col = $val['type'] . '_count';
                $row->$col = $val['part_count'];
            }
        }
        
        // done!
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
     * the $parent_id node. The parts are not save()-able.
     * 
     */
    public function fetchParts($parent_id, $where = null, $order = null)
    {
        $select = Solar::factory('Solar_Sql_Select');
        $select->from($this->_content->nodes, '*');
        // join for area name
        $select->join(
            $this->_content->areas,
            'nodes.area_id = areas.id',
            'name AS area_name'
        );
        $select->where('nodes.parent_id = ?', $parent_id);
        $select->multiWhere($where);
        $select->order($order);
        return $select->fetch('rowset');
    }
    
    /**
     * 
     * Fetch the parts of a parent node ID by their type.
     * 
     * @param int $parent_id The parent node ID.
     * 
     * @param array $type The part-type(s) to fetch.
     * 
     * @param array $order Return in this order.
     * 
     * @return Solar_Sql_Rowset A list of nodes that are children of
     * the $parent_id node. The parts are not save()-able.
     * 
     */
    public function fetchPartsByType($parent_id, $type, $order = null)
    {
        $where = array(
            'nodes.type IN (?)' => (array) $type
        );
        return $this->fetchParts($parent_id, $where, $order);
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
     * Fetches a list of all tags on all nodes of this type with a
     * count of how many each tag occurs.
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
        $select->group('tags.name');
        
        // order and return
        $select->order('tags.name');
        return $select->fetch('pairs');
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
        
        // basic selection
        $select = Solar::factory('Solar_Sql_Select');
        $select->from($this->_content->nodes, '*')
               ->multiWhere($this->_where())
               ->multiWhere($where);
        
        // join for area name
        $select->join(
            $this->_content->areas,
            'nodes.area_id = areas.id',
            'name AS area_name'
        );
        
        // add tags?
        if (! empty($tags)) {
            // force the tags to an array (for the IN(...) clause)
            $tags = $this->_content->tags->asArray($tags);
            $this->_selectTags($select, $tags);
        }
        
        // complete the select
        $select->setPaging($this->_paging);
        $select->order($order);
        $select->limitPage($page);
        
        // fetch data as assoc array keyed on node ID
        $data = $select->fetch('assoc');
        
        // part counts? also check to make sure $data is not empty,
        // thus avoiding SQL errors when finding parts for an empty
        // rowset.
        if ($this->_parts && $data) {
            // fetch and retain part counts
            $part_count = $this->_fetchPartCounts(array_keys($data));
            foreach ($part_count as $val) {
                $id  = $val['id'];
                $col = $val['type'] . '_count';
                $data[$id][$col] = $val['part_count'];
            }
        }
        
        // convert to a Solar_Sql_Rowset and return
        $rowset = Solar::factory('Solar_Sql_Rowset', array('data' => $data));
        $rowset->setSave($this);
        return $rowset;
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
            return $select->setPaging($this->_paging)
                          ->countPages('nodes.id');
        }
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
        $request = Solar::factory('Solar_Request');
        $data['editor_ipaddr'] = $request->server('REMOTE_ADDR');
        
        // force the created timestamp
        $data['created'] = date('Y-m-d\TH:i:s');
        
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
        $request = Solar::factory('Solar_Request');
        $data['editor_ipaddr'] = $request->server('REMOTE_ADDR');
        
        // force the updated timestamp
        $data['updated'] = date('Y-m-d\TH:i:s');
        
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
     * Delete a node, its parts, and its tags.
     * 
     * @param int $id The node ID to delete.
     * 
     * @return void
     * 
     */
    public function delete($id)
    {
        // disallow deletion of all nodes at once.
        if (empty($id) || $id == '0') {
            return;
        }
        
        // delete the node.
        $where = $this->_where();
        $where['nodes.id = ?'] = $id;
        $this->_content->nodes->delete($where);
        
        // delete its parts.
        $where = $this->_where();
        $where['nodes.parent_id = ?'] = $id;
        $this->_content->nodes->delete($where);
        
        // delete its tags.
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
     * Fetch an array of part-counts for specific node IDs.
     * 
     * The returned array is sequential; each element is an array with
     * keys for the node ID, the part-type being counted, and the count
     * of nodes with that part type belonging to the parent node.
     * 
     * There are either one or two entries for each node: one with a
     * zero count (to force the existence of each part-type for that
     * node ID), then a second entry with the actual count (if the node
     * ID has parts of that type).
     * 
     * @param string|array $id_list The IDs to get part counts for.
     * 
     * @return array The part-counts as a sequential array.
     * 
     * 
     */
    protected function _fetchPartCounts($id_list)
    {
        // force to array
        settype($id_list, 'array');
        
        // prepend with zero-counts so that all parts are represented
        $zero = array();
        foreach ($id_list as $id) {
            foreach ($this->_parts as $type) {
                $zero[] = array(
                    'id' => $id,
                    'type' => $type,
                    'part_count' => '0',
                );
            }
        }
        
        // get a list of parent_id, part type, and part count
        $select = Solar::factory('Solar_Sql_Select');
        $select->from('nodes', array(
            'parent_id AS id',
            'type',
            'COUNT(id) AS part_count'
        ));
        $select->where('parent_id IN(?)', $id_list);
        $select->where('type IN(?)', $this->_parts);
        $select->group(array('nodes.parent_id', 'nodes.type'));
        $result = $select->fetch('all');
        
        // append the results to the zero base, and we're done
        $result = array_merge($zero, $result);
        return $result;
    }
}