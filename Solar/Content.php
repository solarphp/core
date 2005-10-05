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
	* A table object representing the broad areas of content.
	* 
	* @access public
	* 
	* @var object Solar_Content_Areas
	* 
	*/
	
	public $areas;
	
	
	/**
	* 
	* A table object representing the container nodes in an area.
	* 
	* @access public
	* 
	* @var object Solar_Content_Nodes
	* 
	*/
	
	public $nodes;
	
	
	/**
	* 
	* A table object representing the content parts within a node.
	* 
	* @access public
	* 
	* @var object Solar_Content_Parts
	* 
	*/
	
	public $parts;
	
	
	/**
	* 
	* A table object representing the edit history of a node-part.
	* 
	* @access public
	* 
	* @var object Solar_Content_Edits
	* 
	*/
	
	public $edits;
	
	
	/**
	* 
	* A table object representing the tags on each node.
	* 
	* @access public
	* 
	* @var object Solar_Content_Tags
	* 
	*/
	
	public $tags;
	
	
	/**
	* 
	* The shared SQL object.
	* 
	* @access protected
	* 
	* @var object Solar_Sql
	* 
	*/
	
	protected $sql;
	
	
	/**
	* 
	* Constructor.
	* 
	*/
	
	public function __construct($config = null)
	{
		// need an sql injection just-in-case
		$this->sql   = Solar::shared('sql');
		
		// the component tables
		$this->areas = Solar::object('Solar_Content_Areas');
		$this->nodes = Solar::object('Solar_Content_Nodes');
		$this->tags  = Solar::object('Solar_Content_Tags');
		$this->parts = Solar::object('Solar_Content_Parts');
		
		// don't actually need edits yet, will start on it
		// when the 'parts' model is ready.
		// 
		//$this->edits = Solar::object('Solar_Content_Edits');
	}
	
	
	// -----------------------------------------------------------------
	// 
	// Areas
	// 
	// -----------------------------------------------------------------
	
	
	public function fetchArea($area)
	{
		return $this->areas->fetchItem($area);
	}
	
	public function fetchAreaList($where = null, $order = null, $page = null)
	{
		return $this->areas->fetchList($where, $order, $page);
	}
	
	public function insertArea($data)
	{
		return $this->areas->table->insert($data);
	}
	
	public function updateArea($area_id, $data)
	{
	}
	
	
	// -----------------------------------------------------------------
	// 
	// Nodes
	// 
	// -----------------------------------------------------------------
	
	
	public function fetchNode($area, $node)
	{
		return $this->nodes->fetchItem($area, $node);
	}
	
	public function fetchNodeList($where = null, $order = null, $page = null)
	{
		return $this->nodes->fetchList($where, $order, $page);
	}
	
	public function insertNode($area_id, $data)
	{	
		/* @todo check if the area exists */
		$data['area_id'] = $area_id;
		
		
		// add a sequential ID.  we do so here, instead of letting the
		// table do it automatically, becuase we may need the ID for the
		// default node name below.
		$data['id'] = $this->nodes->table->increment('id');
		
		// if no name specified, use the ID as the name.
		if (empty($data['name'])) {
			$data['name'] = $data['id'];
		}
		
		// if no owner specified, set as the current user
		if (empty($data['owner_handle'])) {
			$data['owner_handle'] = Solar::shared('user')->auth->username;
		}
		
		// normalize the tag string
		if (! empty($data['tags'])) {
			$data['tags'] = $this->tags->asString($data['tags']);
		}
		
		// attempt the insert
		$node = $this->nodes->table->insert($data);
		if (Solar::isError($node)) {
			// return the error
			return $node;
		}
		
		// add the tags, too
		$tags = $this->refreshTags($node['id'], $node['tags']);
		if (Solar::isError($tags)) {	
			// return the error
			return $tags;
		} else {
			// return the new node data
			return $node;
		}
	}
	
	public function updateNode($node_id, $data)
	{
		$data['node_id'] = (int) $node_id;
		
		// normalize the tag string if one was passed in.
		if (isset($data['tags'])) {
			$data['tags'] = $this->tags->asString($data['tags']);
		}
		
		// update the node
		$where = array('node_id', (int) $node_id);
		$node = $this->nodes->table->update($data, $where);
		if (Solar::isError($data)) {
			return $node;
		}
		
		// refresh the tags
		if (isset($data['tags'])) {
			$this->refreshTags($data['node_id'], $data['tags']);
		}
		
		// done
		return $node;
	}
	
	
	// -----------------------------------------------------------------
	// 
	// Parts
	// 
	// -----------------------------------------------------------------
	
	
	public function insertPart($area_id, $node_id, $data)
	{
		/* @todo check if the area exists */
		$data['area_id'] = (int) $area_id;
		/* @todo check if the node exists */
		$data['node_id'] = (int) $node_id;
		return $this->parts->table->insert($data);
	}
	
	public function updatePart($part_id, $data)
	{
		$where = array('part_id', (int) $part_id);
		return $this->parts->table->update($data, $where);
	}
	
	public function revisePart($part_id, $data)
	{
		// copy the old part to edits
		// update the part in place
	}
	
	
	// -----------------------------------------------------------------
	// 
	// Tags
	// 
	// -----------------------------------------------------------------
	
	public function refreshTags($node_id, $tags)
	{
		$this->tags->refresh($node_id, $tags);
	}
	
	
}
?>