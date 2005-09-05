<?php

/**
* 
* Tags on nodes.
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

class Solar_Content_Tags extends Solar_Sql_Table {
	
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
		$this->name = 'tags';
		
		// -------------------------------------------------------------
		// 
		// COLUMNS
		// 
		
		// unique ID for this part
		$this->colDefine('tag_id', 'int');
		$this->colRequire('tag_id');
		$this->colSequence('tag_id');
		
		// the area of the node for this tag
		$this->colDefine('tag_area_name', 'varchar', 127);
		$this->colRequire('tag_area_name');
		$this->colValid('tag_area_name', 'word');
		
		// the node for this tag
		$this->colDefine('tag_node_name', 'varchar', 127);
		$this->colRequire('tag_node_name');
		$this->colValid('tag_node_name', 'word');
		
		// the "owner" of the node to which this tag applies
		$this->colDefine('tag_user_handle', 'varchar', 32);
		$this->colRequire('tag_user_handle');
		
		// the tag itself
		$this->colDefine('tag_name', 'varchar', 64);
		$this->colRequire('tag_name');
		$this->colValid('tag_name', 'word');
		
		
		// -------------------------------------------------------------
		// 
		// KEYS AND INDEXES
		// 
		
		$this->primary('tag_id');
		$this->unique('tag_id');
		$this->index('tag_area_name');
		$this->index('tag_node_name');
		$this->index('tag_user_handle');
		$this->index('tag_name');
	}
}
?>