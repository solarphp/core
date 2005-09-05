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
		$this->tags = Solar::object('Solar_Content_Tags');
	}
}
?>