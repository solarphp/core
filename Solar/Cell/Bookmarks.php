<?php

/**
* 
* Bookmarks management class.
* 
* All bookmarks go in a single area (Solar_Cell_Bookmarks by default).
* Each node is one bookmark for one owner.
* 
* @category Solar
* 
* @package Solar_Cell
* 
* @subpackage Solar_Cell_Bookmarks
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* 
* Bookmarks management class.
* 
* @category Solar
* 
* @package Solar_Cell
* 
* @subpackage Solar_Cell_Bookmarks
* 
*/

class Solar_Cell_Bookmarks extends Solar_Base {
	
	
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
		'area_name'    => 'Solar_Cell_Bookmarks',
		'default_tags' => 'inbox',
		'paging'       => 10,
	);
	
	
	/**
	* 
	* An all-purpose content object.
	* 
	* @access protected
	* 
	* @var object Solar_Content
	* 
	*/
	
	protected $content;
	
	
	/**
	* 
	* The area ID where all bookmarks for all users are stored, by ID.
	* 
	* @access protected
	* 
	* @var int
	* 
	*/
	
	protected $area_id;
	
	
	/**
	* 
	* The node type for all bookmarks.
	* 
	* @access protected
	* 
	* @var object Solar_Sql
	* 
	*/
	
	protected $node_type = 'bookmark';
	
	
	/**
	* 
	* A convenient baseline WHERE array when searching for bookmarks.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $where = array(
		'nodes.area_id = ?' => null,
		'nodes.type = ?'    => null,
	);
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	* @param array $config An array of configuration options.
	* 
	*/
	
	public function __construct($config = null)
	{
		// main construction
		parent::__construct($config);
		
		// create a content object and set its paging
		$this->content = Solar::object('Solar_Content');
		$this->paging($this->config['paging']);
		
		// make sure there is a content area for bookmarks
		$name = $this->config['area_name'];
		$area = $this->content->fetchArea($name);
		if (empty($area)) {
			// area didn't exist, create it.
			$data = array('name'  => $name);
			$area = $this->content->insertArea($data);
		}
		
		// save the bookmarks area ID as a property
		$this->area_id = $area['id'];
		
		// set up a baseline WHERE clause for searches
		$this->where = array(
			'nodes.area_id = ?' => $this->area_id,
			'nodes.type = ?'    => $this->node_type,
		);
	}
	
	
	/**
	* 
	* Sets paging in the content object.
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
		$this->content->paging((int) $val);
	}
	
	
	/**
	* 
	* Insert a new bookmark node.
	* 
	* @access public
	* 
	* @param array $data The bookmark data.
	* 
	* @return array The inserted data.
	* 
	*/
	
	public function insert($data)
	{
		// force the area and type
		$data['area_id'] = $this->area_id;
		$data['type']    = $this->node_type;
		
		// force a default tagstring if empty or blank
		if (empty($data['tags']) || trim($data['tags']) == '') {
			$data['tags'] = $this->config['default_tags'];
		}
		
		// attempt the insert
		return $this->content->insertNode($data);
	}
	
	
	/**
	* 
	* Update a bookmark node.
	* 
	* @access public
	* 
	* @param array $data The bookmark data.
	* 
	* @return array The updated data.
	* 
	*/
	
	public function update($node_id, $data)
	{
		// force the area_id and type
		$data['area_id'] = $this->area_id;
		$data['type']    = $this->node_type;
		
		// if tags are going to be updated as blank,
		// force in the default tag set
		if (isset($data['tags']) && trim($data['tags']) == '') {
			$data['tags'] = $this->config['default_tags'];
		}
		
		// update the node
		return $this->content->updateNode(
			$node_id,
			$data
		);
	}
	
	
	/**
	* 
	* Fetch the default bookmark node data.
	* 
	* @access public
	* 
	* @return array Default data for a new bookmark node.
	* 
	*/
	
	public function fetchDefault()
	{
		// a default generic node
		$data = $this->content->defaultNode();
		
		// default for bookmarks
		$data['area_id']      = $this->area_id;
		$data['type']         = $this->node_type;
		$data['uri']          = Solar::get('uri');
		$data['subj']         = Solar::get('subj');
		$data['owner_handle'] = Solar::shared('user')->auth->username;
		$data['tags']         = $this->config['default_tags'];
		return $data;
	}
	
	
	/**
	* 
	* Fetch one bookmark (by node ID) from the content store.
	* 
	* @access public
	* 
	* @param int $id The bookmark node ID.
	* 
	* @return array The bookmark data.
	* 
	*/
	
	public function fetchItem($id)
	{
		$where = $this->where;
		$where['nodes.id = ?'] = (int) $id;
		$data = $this->content->fetchNode($where);
		return $data;
	}
	
	
	/**
	* 
	* Fetch a list of bookmarks from the content store.
	* 
	* You can specify an owner_handle (username) and/or a list of
	* tags to filter the list.
	* 
	* @access public
	* 
	* @param string $handle The owner_handler (username) to fetch
	* bookmarks for; if empty, fetches for all owners.
	* 
	* @param string|array $tags Fetch bookmarks with all these
	* tags; if empty, fetches for all tags.
	* 
	* @param string|array $order Order in this fashion; if empty,
	* orders by creation-timestamp descending (most-recent first).
	* 
	* @param int $page Which page-number of results to fetch.
	* 
	* @return array The list of bookmarks.
	* 
	*/
	
	public function fetchList($handle = null, $tags = null, $order = null,
		$page = null)
	{
		$where = $this->where;
		if ($handle) {
			$where['nodes.owner_handle = ?'] = $handle;
		}
		
		if (empty($order)) {
			$order = 'nodes.created DESC';
		}
		
		return $this->content->fetchNodeList($tags, $where, $order, $page);
	}
	
	
	/**
	* 
	* Fetch a total count and pages of bookmarks in the content store.
	* 
	* You can specify an owner_handle (username) and/or a list of
	* tags to limit the count.
	* 
	* @access public
	* 
	* @param string $handle The owner_handler (username) to count
	* bookmarks for; if empty, counts for all owners.
	* 
	* @param string|array $tags Count bookmarks with all these
	* tags; if empty, counts for all tags.
	* 
	* @return array A array with keys 'count' (total number of 
	* bookmarks) and 'pages' (number of pages).
	* 
	*/
	
	public function fetchCount($handle = null, $tags = null)
	{
		$where = $this->where;
		if ($handle) {
			$where['nodes.owner_handle = ?'] = $handle;
		}
		
		return $this->content->fetchNodeCount($tags, $where);
	}
	
	
	/**
	* 
	* Fetch a list of all bookmark tags.
	* 
	* You can specify an owner_handle (username) to limit the list.
	* 
	* @access public
	* 
	* @param string $handle The owner_handler (username) to list
	* bookmark tags for; if empty, lists for all owners.
	* 
	* @return array An array where the key is the tag name and
	* the value is the number of times that tag appears.
	* 
	*/
	
	public function fetchTagList($handle = null)
	{
		$where = $this->where;
		if ($handle) {
			$where['nodes.owner_handle = ?'] = $handle;
		}
		return $this->content->fetchTagList($where);
	}
	
	
	/**
	* 
	* Fetch a bookmark by owner_handler (username) and URI.
	* 
	* Useful for seeing if an owner has already bookmarked a URI.
	* 
	* @access public
	* 
	* @param string $handle The owner_handler (username).
	* 
	* @return string $uri The URI to look form
	* 
	* @return array The node data.
	* 
	*/
	
	public function fetchOwnerUri($handle, $uri)
	{
		$where = $this->where;
		$where['nodes.owner_handle = ?'] = $handle;
		$where['nodes.uri = ?']          = $uri;
		return $this->content->fetchNode($where);	
	}
	
	
	/**
	* 
	* Deletes one bookmark by node ID.
	* 
	* @access public
	* 
	* @param int $id The node ID to delete.
	* 
	* @return mixed
	* 
	*/
	
	public function delete($id)
	{
		$where = $this->where;
		$where['id = ?'] = (int) $id;
		return $this->content->deleteNodes($where);
	}
	
	
	/**
	* 
	* Generates a data-entry form for a single bookmark.
	* 
	* @access public
	* 
	* @param int|array $data An existing node ID, or an array of data to
	* pre-populate into the form.  The array should have a key
	* 'bookmarks' with a sub-array using keys for 'uri', 'subj', 'summ',
	* 'tags', and 'rank'.  If empty, default values are pre-populated
	* into the form.
	* 
	* @return object A Solar_Form object.
	* 
	*/
	
	public function form($data = null)
	{
		// which node columns do we want?
		$cols = array('id', 'uri', 'subj', 'summ', 'tags', 'rank');
		
		// extra info for the form elements
		$info['uri']['attribs']['size']  = 48;
		$info['subj']['attribs']['size'] = 48;
		$info['summ']['attribs']['size'] = 48;
		$info['tags']['attribs']['size'] = 48;
		$info['rank']['attribs']['size'] = 3;
		
		// get the form with a set of 'bookmarks' elements
		$form = $this->content->form(
			get_class($this),
			$cols,
			$info,
			'bookmarks'
		);
		
		// what data should we populate into the form?
		if (empty($data)) {
			// defaults
			$data = array(
				'bookmarks' => $this->fetchDefault()
			);
		} elseif (is_numeric($data)) {
			// $data is a node ID, look it up for values
			$data = array(
				'bookmarks' => $this->fetchItem((int) $data)
			);
		}
		
		// populate basic data into the form and return
		$form->populate($data);
		return $form;
	}
}
?>