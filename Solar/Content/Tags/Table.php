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
* @version $Id: Tags.php 527 2005-09-28 14:57:17Z pmjones $
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

class Solar_Content_Tags_Table extends Solar_Sql_Table {
	
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
		
		// the node_id for this tag (which carries the area_id with it)
		$this->col['node_id'] = array(
			'type'    => 'int',
			'require' => true,
		);
		
		// the tag itself
		$this->col['name'] = array(
			'type'    => 'varchar',
			'size'    => 64,
			'require' => true,
			'valid'   => 'word',
		);
		
		
		// -------------------------------------------------------------
		// 
		// KEYS AND INDEXES
		// 
		
		$this->idx = array(
			'name' => 'normal',
		);
	}
}
?>