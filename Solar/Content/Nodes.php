<?php

/**
* 
* Nodes within an area, equivalent to containers for related content parts.
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

Solar::loadClass('Solar_Sql_Table');


/**
* 
* Broad content areas equivalent to logical namespaces.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Content
* 
*/

class Solar_Content_Nodes extends Solar_Sql_Table {
	
	
	/**
	* 
	* Schema setup.
	* 
	* @access protected
	* 
	* @return void
	* 
	*/
	
	protected function setup()
	{
		// the table name
		$this->name = 'nodes';
		
		// -------------------------------------------------------------
		// 
		// COLUMNS
		// 
		
		// the area in which this node belongs
		$this->colDefine('node_area_name', 'varchar', 127);
		$this->colRequire('node_area_name');
		$this->colValid('node_area_name', 'word');
		
		// the node name
		$this->colDefine('node_name', 'varchar', 127);
		$this->colRequire('node_name');
		$this->colValid('node_name', 'word');
		
		// the node "subject" or title
		$this->colDefine('node_subj', 'varchar', 255);
		
		// the node tags, made of a-zA-Z0-9_ and space
		$this->colDefine('node_tags', 'varchar', 255);
		$this->colValid('node_tags', 'regex', '/^[\w ]*$/');
		
		// the user who owns this area
		$this->colDefine('node_user_handle', 'varchar', 32);
		$this->colValid('node_user_handle', 'word');
		
		// arbitrary list-order, sequence, or ranking
		$this->colDefine('node_rank', 'int');
		
		// arbitrary user-assigned rating, score, level, or value
		$this->colDefine('node_rating', 'int');
		
		// serialized array of preferences for this node
		$this->colDefine('node_prefs', 'clob');
		
		// -------------------------------------------------------------
		// 
		// KEYS AND INDEXES
		// 
		
		// primary key is a composite
		$this->primary('node_area_name', 'node_name');
		
		// composite unique index
		$this->unique('full_name', 'node_area_name', 'node_name');
		
		// the remaining indexes
		$this->index('node_area_name');
		$this->index('node_name');
		$this->index('node_tags');
		$this->index('node_rank');
		$this->index('node_rating');
	}
	
	public function fetchList($node_area_name, $page = null)
	{
		$where = array('node_area_name' => $node_area_name);
		$order = 'LOWER(node_name) ASC';
		return parent::fetchList($where, $order, $page);
	}
}
?>