<?php

/**
* 
* Generic content management class.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Content
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* Needed by all the table objects.
*/
Solar::loadClass('Solar_Sql_Table');

/**
* 
* Generic content management class.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Content
* 
* @todo Build in content permission system.
* 
*/

class Solar_Content extends Solar_Base {
	
	
	/**
	* 
	* User-defined configuration options.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $config = array(
		'paging' => 10
	);
	
	
	/**
	* 
	* A table object representing the broad areas of content.
	* 
	* @access protected
	* 
	* @var object Solar_Content_Areas
	* 
	*/
	
	protected $areas;
	
	
	/**
	* 
	* A table object representing the container nodes in an area.
	* 
	* @access protected
	* 
	* @var object Solar_Content_Nodes
	* 
	*/
	
	protected $nodes;
	
	
	/**
	* 
	* A table object representing the searchable tags on each node.
	* 
	* @access protected
	* 
	* @var object Solar_Content_Tags
	* 
	*/
	
	protected $tags;
	
	
	/**
	* 
	* Pages have this many rows each.
	* 
	* @access protected
	* 
	* @var int
	* 
	*/
	
	protected $paging = 10;
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	* @param array $config User-defined configuration options.
	* 
	*/
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->paging($this->config['paging']);
		
		// the component tables
		$this->areas = Solar::object('Solar_Content_Areas');
		$this->nodes = Solar::object('Solar_Content_Nodes');
		$this->tags  = Solar::object('Solar_Content_Tags');
	}
	
	
	/**
	* 
	* Sets the number of rows per page.
	* 
	* @access public
	* 
	* @param int $val The number of rows per page.
	* 
	* @return void
	* 
	*/
	
	public function paging($val)
	{
		$this->paging = (int) $val;
	}
	
	
	/**
	* 
	* Creates a form based on the nodes table columns.
	* 
	* @access public
	* 
	* @param string $class The class to use for locales.
	* 
	* @param array $cols The node columns to use.
	* 
	* @param array $info Additional form information to 
	* override the defaults.
	* 
	* @param string $array_name Create form elements as part
	* of this array name.
	* 
	* @return object A Solar_Form object.
	* 
	*/
	
	public function form($class = null, $cols = null, $info = null, $array_name = null)
	{
		// the basic form object
		$form = Solar::object('Solar_Form');
		
		// the class for locales
		if (empty($class)) {
			$class = 'Solar_Content_Nodes';
		}
		
		// which columns to include in the form, and in which order
		if (empty($cols)) {
			$cols = array_keys($this->nodes->col);
		}
		
		// set the form element labels and descriptions
		settype($info, 'array');
		foreach ($cols as $col) {
			$info[$col]['label'] = Solar::locale($class, 'LABEL_' . strtoupper($col));
			$info[$col]['descr'] = Solar::locale($class, 'DESCR_' . strtoupper($col));
		}
		
		// load from the nodes table column definitions
		$form->load(
			'Solar_Form_Load_Table',
			$this->nodes,
			$info,
			$array_name
		);
		
		// done!
		return $form;
	}
	
	
	// -----------------------------------------------------------------
	// 
	// Areas
	// 
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Fetch one area by ID or name.
	* 
	* @access public
	* 
	* @param int|string $area
	* 
	* @return array An array of area information.
	* 
	*/
	
	public function fetchArea($area)
	{
		$select = $this->selectAreas();
		
		if (is_numeric($area)) {
			$select->where("id = ?", $area);
		} else {
			$select->where("name = ?", $area);
		}
		
		$select->order('id');
		return $select->fetch('row');
	}
	
	
	/**
	* 
	* Fetch a list of areas.
	* 
	* @access public
	* 
	* @param array $where A list of multiWhere() conditions.
	* 
	* @param array $order Order results in this fashion.
	* 
	* @param int $page Fetch this page of the results.
	* 
	* @return array An array of information about all the fetched areas.
	* 
	*/
	
	public function fetchAreaList($where = null, $order = null, $page = null)
	{
		$select = $this->selectAreas($where);
		$select->order($order);
		$select->limitPage($page);
		return $select->fetch('all');
	}
	
	
	/**
	* 
	* Inserts one area.
	* 
	* @access public
	* 
	* @param array $data The data to insert.
	* 
	* @return array The data as inserted.
	* 
	*/
	
	public function insertArea($data)
	{
		return $this->areas->insert($data);
	}
	
	
	/**
	* 
	* Updates one area in-place.
	* 
	* @access public
	* 
	* @param int $id The area ID to update.
	* 
	* @param array $data Update with this data.
	* 
	* @return array The data as updated.
	* 
	*/
	
	public function updateArea($id, $data)
	{
		$where = array('id = ?' => $id);
		return $this->areas->update($data, $where);
	}
	
	
	/**
	* 
	* Returns a selection object for custom-where searching of areas.
	* 
	* @access public
	* 
	* @param array $where A set of multiWhere() conditions.
	* 
	* @return object A Solar_Sql_Select object prepared for searching by
	* tags and the custom WHERE conditions.
	* 
	*/
	
	public function selectAreas($where = null)
	{
		$select = Solar::object('Solar_Sql_Select');
		$select->from($this->areas, '*');
		$select->multiWhere($where);
		$select->paging($this->paging);
		return $select;
	}
	
	
	// -----------------------------------------------------------------
	// 
	// Nodes
	// 
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Fetches one node by name, ID, or where conditions.
	* 
	* @access public
	* 
	* @param mixed $spec An integer ID, a string name, or an array
	* of multiWhere() conditions to specify which node to fetch.
	* 
	* @return array The first node that matches the search spec.
	* 
	*/
	
	public function fetchNode($spec)
	{
		if (is_array($spec)) {
		
			// it's a series of where conditions (null for tags)
			$select = $this->selectNodes(null, $spec);
			
		} else {
		
			// the baseline select
			$select = $this->selectNodes();
			
			// it's an ID or name
			if (is_numeric($spec)) {
				$select->where('id = ?', $spec);
			} else {
				$select->where("name = ?", $spec);
			}
		}
		
		$select->order('nodes.id');
		return $select->fetch('row');
	}
	
	
	/**
	* 
	* Fetches a list of nodes.
	* 
	* @access public
	* 
	* @param string|array $tags Fetch nodes with these tags.
	* 
	* @param array $where Fetch nodes matching these multiWhere()
	* conditions.
	* 
	* @param string|array $order Order in this fashion.
	* 
	* @param int $page Fetch this page-number of results.
	* 
	* @return array The nodes that match the search specifications.
	* 
	*/
	
	public function fetchNodeList($tags = null, $where = null, $order = null,
		$page = null)
	{
		$select = $this->selectNodes($tags, $where);
		$select->order($order);
		$select->limitPage($page);
		return $select->fetch('all');
	}
	
	
	/**
	* 
	* Fetches the total count and pages of matching nodes.
	* 
	* Normally, we would use select->fetchCount(), but the GROUP
	* and HAVING clauses related to tag-based searches get in the
	* way of that.  This stopgap measure is a fat, slow, stupid 
	* hack until I can figure out a more elegant solution.
	* 
	* @access public
	* 
	* @param string|array $tags Fetch nodes with these tags.
	* 
	* @param array $where Fetch nodes matching these multiWhere()
	* conditions.
	* 
	* @return array An array with keys 'count' (the total number of
	* nodes matching) and 'pages' (the total number of pages).
	* 
	*/
	
	public function fetchNodeCount($tags = null, $where = null)
	{
		$select = Solar::object('Solar_Sql_Select');
		$select->from($this->nodes, 'id');
		
		if (! empty($tags)) {
			// force the tags to an array (for the IN(...) clause)
			$tags = $this->tags->asArray($tags);
			
			// build and return the select statement
			$select->join($this->tags, 'tags.node_id = nodes.id');
			$select->where('tags.name IN (?)', $tags);
			$select->group('nodes.id');
			$select->having("COUNT(nodes.id) = ?", count($tags));
		}
		
		// add custom where conditions
		$select->multiWhere($where);
		
		// fetch all rows and count how many we got (fat, stupid, slow)
		$all = $select->fetch('all');
		$result = count($all);
		unset($all);
		
		// $result is the row-count; how many pages does it convert to?
		$pages = 0;
		if ($result > 0) {
			$pages = ceil($result / $this->paging);
		}
		
		// done!
		return array(
			'count' => $result,
			'pages' => $pages
		);
	}
	
	
	/**
	* 
	* Get the default values for a new generic node.
	* 
	* @access public
	* 
	* @return array An array of default node data.
	* 
	*/
	
	public function defaultNode()
	{
		return $this->nodes->getDefault();
	}
	
	
	/**
	* 
	* Inserts one node.
	* 
	* Updates the 'tags' search table as well.
	* 
	* @access public
	* 
	* @param array $data The data to insert.
	* 
	* @return array The data as inserted.
	* 
	*/
	
	public function insertNode($data)
	{	
		// normalize the tag string if one exists
		if (! empty($data['tags'])) {
			$data['tags'] = $this->tags->asString($data['tags']);
		}
		
		// attempt the insert
		$data = $this->nodes->insert($data);
		if (Solar::isError($data)) {
			// return the error
			return $data;
		}
		
		// add the tags to the tag-search table
		$tags = $this->tags->refresh($data['id'], $data['tags']);
		if (Solar::isError($tags)) {	
			// return the error
			return $tags;
		}
		
		// return the new node data
		return $data;
	}
	
	
	/**
	* 
	* Updates one node in-place.
	* 
	* Updates the 'tags' search table as well.
	* 
	* @access public
	* 
	* @param int $id The node ID to update.
	* 
	* @param array $data Update with this data.
	* 
	* @return array The data as updated.
	* 
	*/
	
	public function updateNode($id, $data)
	{
		// normalize the tag string if one was passed in.
		if (! empty($data['tags'])) {
			$data['tags'] = $this->tags->asString($data['tags']);
		}
		
		// update the node
		$where = array('id = ?' => (int) $id);
		$data = $this->nodes->update($data, $where);
		if (Solar::isError($data)) {
			return $data;
		}
		
		// refresh the tags
		if (! empty($data['tags'])) {
			$this->tags->refresh($data['id'], $data['tags']);
		}
		
		// done
		return $data;
	}
	
	
	/**
	* 
	* Deletes nodes.
	* 
	* @access public
	* 
	* @param array $where A list of multiWhere() conditions to specify
	* which nodes to delete.
	* 
	* @return void
	* 
	*/
	
	public function deleteNodes($where)
	{
		// find out which nodes are getting deleted
		$select = Solar::object('Solar_Sql_Select');
		$select->from($this->nodes, 'id');
		$select->multiWhere($where);
		$id_list = $select->fetch('col');
		
		// delete the nodes themselves
		$result = $this->nodes->delete($where);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// delete the search tags
		$where = array(
			'node_id IN (?)' => $id_list
		);
		$result = $this->tags->delete($where);
		return $result;
	}
	
	
	/**
	* 
	* Returns a selection object for tag- and custom-where searching.
	* 
	* This search algorithm is derived from...
	* 
	* 	http://www.pui.ch/phred/archives/2005/04/tags-database-schemas.html
	* 	http://www.petercooper.co.uk/archives/000648.html
	* 	http://www.bigbold.com/snippets/posts/show/32
	* 	
	* SELECT nodes.*
	* FROM nodes
	* JOIN tags ON tags.node_id = nodes.id
	* WHERE tags.name IN ('this', 'that', 'other') -- count = 3
	* GROUP BY nodes.id
	* HAVING COUNT(nodes.id) = 3 -- count = 3
	* 
	* The select is an intersection (AND).  To make it union (OR),
	* drop the HAVING clause and add DISTINCT.
	* 
	* @access public
	* 
	* @param string|array $tags Fetch nodes with all of these tags.
	* This can be a space-separated list or a sequential array.
	* 
	* @param array $where A set of multiWhere() conditions.
	* 
	* @return object A Solar_Sql_Select object prepared for searching by
	* tags and the custom WHERE conditions.
	* 
	*/
	
	public function selectNodes($tags = null, $where = null)
	{
		$select = Solar::object('Solar_Sql_Select');
		$select->from($this->nodes, '*');
		
		if (! empty($tags)) {
			// force the tags to an array (for the IN(...) clause)
			$tags = $this->tags->asArray($tags);
			
			// build and return the select statement
			$select->join($this->tags, 'tags.node_id = nodes.id');
			$select->where('tags.name IN (?)', $tags);
			$select->group('nodes.id');
			$select->having("COUNT(nodes.id) = ?", count($tags));
		}
		
		// add the custom where conditions from an array
		$select->multiWhere($where);
		$select->paging($this->paging);
		return $select;
	}
	
	
	// -----------------------------------------------------------------
	// 
	// Tags
	// 
	// -----------------------------------------------------------------
	
	
	
	/**
	* 
	* Fetch tag names and counts.
	* 
	* @access public
	* 
	* @param array $where A set of multiWhere() conditions to filter
	* the tags returned.
	* 
	* @return object A Solar_Sql_Select object prepared for searching by
	* tags and the custom WHERE conditions.
	* 
	*/
	
	// fetch all tags (regardless of type) and their counts as name =>
	// count, ordered by name, optionally for a specific user,
	// optionally having a minimum count.
	public function fetchTagList($where = null)
	{
		$select = Solar::object('Solar_Sql_Select');
		$select->from(
			$this->tags,
			array('name', 'COUNT(tags.id) AS rank')
		);
		
		// join to the nodes table
		$select->join('nodes', 'tags.node_id = nodes.id');
		
		// add custom where conditions
		$select->multiWhere($where);
		
		// grouping
		$select->group('name');
		
		// order and return
		$select->order('name');
		return $select->fetch('pairs');
	}
	
	
}
?>