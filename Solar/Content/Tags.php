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
		$this->col['id'] = array(
			'type'    => 'int',
			'require' => true,
			'seqname' => 'id',
			'primary' => true,
		);
		
		// the area of the node for this tag
		$this->col['areas_name'] = array(
			'type'    => 'varchar',
			'size'    => 127,
			'require' => true,
			'valid'   => array(
				array('word'),
			),
		);
		
		// the node for this tag
		$this->col['nodes_name'] = array(
			'type'    => 'varchar',
			'size'    => 127,
			'require' => true,
			'valid'   => array(
				array('word'),
			),
		);
		
		
		// the "owner" of the node to which this tag applies
		$this->col['users_handle'] = array(
			'type'    => 'varchar',
			'size'    => 32,
			'require' => true,
		);
		
		// the tag itself
		$this->col['name'] = array(
			'type'    => 'varchar',
			'size'    => 64,
			'require' => true,
			'valid'   => array(
				array('word'),
			),
		);
		
		
		// -------------------------------------------------------------
		// 
		// KEYS AND INDEXES
		// 
		
		$this->idx = array(
			'id' => 'unique',
			'areas_name' => 'normal',
			'nodes_name' => 'normal',
			'users_handle' => 'normal',
			'name' => 'normal',
		);
	}
}
?>