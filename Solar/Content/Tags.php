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
* @version $Id:$
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
	
	protected setup()
	{
		// the table name
		$this->name = 'tags';
		
		// -------------------------------------------------------------
		// 
		// COLUMNS
		// 
		
		// unique ID for this part
		$this->column('tag_id', 'int');
		$this->require('tag_id');
		$this->sequence('tag_id');
		
		// the area of the node for this tag
		$this->column('tag_area_name', 'varchar', 127);
		$this->require('tag_area_name');
		$this->validate('tag_area_name', 'word');
		
		// the node for this tag
		$this->column('tag_node_name', 'varchar', 127);
		$this->require('tag_node_name');
		$this->validate('tag_node_name', 'word');
		
		// the "owner" of the node to which this tag applies
		$this->column('tag_user_handle', 'varchar', 32);
		$this->require('tag_user_handle');
		
		// the tag itself
		$this->column('tag_name', 'varchar', 64);
		$this->require('tag_name');
		$this->validate('tag_name', 'word');
		
		
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