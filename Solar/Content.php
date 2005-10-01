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
*/

class Solar_Content extends Solar_Base {
	
	
	/**
	* 
	* A table object representing the broad areas of content.
	* 
	* @access protected
	* 
	* @var object Solar_Content_Areas
	* 
	*/
	
	public $areas;
	
	
	/**
	* 
	* A table object representing the container nodes in an area.
	* 
	* @access protected
	* 
	* @var object Solar_Content_Nodes
	* 
	*/
	
	public $nodes;
	
	
	/**
	* 
	* A table object representing the content parts within a node.
	* 
	* @access protected
	* 
	* @var object Solar_Content_Parts
	* 
	*/
	
	public $parts;
	
	
	/**
	* 
	* A table object representing the edit history of a node-part.
	* 
	* @access protected
	* 
	* @var object Solar_Content_Edits
	* 
	*/
	
	public $edits;
	
	
	/**
	* 
	* A table object representing the tags on each node.
	* 
	* @access protected
	* 
	* @var object Solar_Content_Tags
	* 
	*/
	
	public $tags;
	
	protected $sql;
	
	/**
	* 
	* Constructor.
	* 
	*/
	
	public function __construct($config = null)
	{
		$this->areas = Solar::object('Solar_Content_Areas');
		$this->nodes = Solar::object('Solar_Content_Nodes');
		$this->parts = Solar::object('Solar_Content_Parts');
		$this->edits = Solar::object('Solar_Content_Edits');
		$this->tags  = Solar::object('Solar_Content_Tags');
		
		$this->sql   = Solar::shared('sql');
	}
	
	// -----------------------------------------------------------------
	// 
	// Nodes
	// 
	// -----------------------------------------------------------------
	
	
	public function nodeInsert($data)
	{
		if (! $this->areaExists($area)) {
			$tmp = array(
				'name' => $area,
				'users_handle' => Solar::shared('user')->auth->username,
			);
			$this->areaInsert($data);
		}
		
		return $this->nodes->table->insert($data);
	}
	
	// -----------------------------------------------------------------
	// 
	// Parts
	// 
	// -----------------------------------------------------------------
	
	
	// this is a new part entirely
	public function partInsert($area, $node, $data)
	{
		if (! $this->nodeExists($area, $node)) {
			$tmp = array(
				'name' => $node,
				'users_handle' => Solar::shared('user')->auth->username,
			);
			$this->nodeInsert($area, $data);
		}
		
		// now insert the part and its first edit.
		
	}
	
	// this is a new edit
	public function partUpdate($area, $node, $id, $data)
	{
	}
	
	// this is an update-in-place
	public function partReplace($area, $node, $id, $data)
	{
	}
	

}
?>