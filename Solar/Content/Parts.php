<?php

/**
* 
* Individual parts of content within a node: wiki page, file atch, comment, etc.
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

class Solar_Content_Parts extends Solar_Sql_Table {
	
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
		$this->name = 'parts';
		
		// -------------------------------------------------------------
		// 
		// COLUMNS
		// 
		
		// unique ID for this part
		$this->column('part_id', 'int');
		$this->require('part_id');
		$this->sequence('part_id');
		
		// the area in which this part belongs
		$this->column('part_area_name', 'varchar', 127);
		$this->require('part_area_name');
		$this->validate('part_area_name', 'word');
		
		// the node in which this part belongs
		$this->column('part_node_name', 'varchar', 127);
		$this->require('part_node_name');
		$this->validate('part_node_name', 'word');
		
		// the arbitrary part type: wiki, blog, news, comment, trackback, etc.
		$this->column('part_type', 'varchar', 32);
		$this->require('part_type');
		$this->validate('part_type', 'word');
		
		// the locale for this part
		$this->column('part_locale', 'char', 5);
		$this->require('part_locale');
		$this->default('part_locale', 'literal', 'en_US');
		$this->validate('part_locale', 'locale');
		
		// the most-recent edit ID number
		$this->column('part_edit_id', 'int');
		$this->require('part_edit_id');
		
		// the user who owns this part
		$this->column('part_user_handle', 'varchar', 32);
		$this->validate('part_user_handle', 'word');
		
		// arbitrary list-order, sequence, or ranking
		$this->column('part_rank', 'int');
		
		// arbitrary user-assigned rating, score, level, or value
		$this->column('part_rating', 'int');
		
		// serialized array of preferences for this part
		$this->column('part_prefs', 'clob');
		
		
		// -------------------------------------------------------------
		// 
		// KEYS AND INDEXES
		// 
		
		$this->primary('part_id');
		$this->unique('part_id');
		$this->index('part_area_name');
		$this->index('part_node_name');
		$this->index('part_type');
		$this->index('part_locale');
		$this->index('part_edit_id');
		$this->index('part_rank');
		$this->index('part_rating');
	}
}
?>