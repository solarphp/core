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
	
	protected setup()
	{
		// the table name
		$this->name = 'nodes';
		
		// -------------------------------------------------------------
		// 
		// COLUMNS
		// 
		
		// the area in which this node belongs
		$this->column('node_area_name', 'varchar', 127);
		$this->require('node_area_name');
		$this->validate('node_area_name', 'word');
		
		// the node name
		$this->column('node_name', 'varchar', 127);
		$this->require('node_name');
		$this->validate('node_name', 'word');
		
		// the node "subject" or title
		$this->column('node_subj', 'varchar', 255);
		
		// the node tags, made of a-zA-Z0-9_ and space
		$this->column('node_tags', 'varchar', 255);
		$this->validate('node_tags', 'regex', '/^[\w ]*$/');
		
		// the user who owns this area
		$this->column('node_user_handle', 'varchar', 32);
		$this->validate('node_user_handle', 'word');
		
		// arbitrary list-order, sequence, or ranking
		$this->column('node_rank', 'int');
		
		// arbitrary user-assigned rating, score, level, or value
		$this->column('node_rating', 'int');
		
		// serialized array of preferences for this node
		$this->column('node_prefs', 'clob');
		
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
}
?>