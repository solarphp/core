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
		$this->col['areas_name'] = array(
			'type'    => 'varchar',
			'size'    => 127,
			'require' => true,
			'primary' => true,
			'valid'   => array(
				array('word'),
			),
		);
		
		// the node name
		$this->col['name'] = array(
			'type'    => 'varchar',
			'size'    => 127,
			'require' => true,
			'primary' => true,
			'valid'   => array(
			),
		);
		
		// the node "subject" or title
		$this->col['subj'] = array(
			'type'    => 'varchar',
			'size'    => 255,
		);
		
		// the node tags, made of a-zA-Z0-9_ and space
		$this->col['tags'] = array(
			'type'    => 'varchar',
			'size'    => 255,
			'valid'   => array(
				array('regex', '/^[\w ]*$/'),
			),
		);
		
		// the user who owns this area
		$this->col['users_handle'] = array(
			'type'    => 'varchar',
			'size'    => 32,
			'valid'   => array(
				array('word'),
			),
		);
		
		// arbitrary list-order, sequence, or ranking
		$this->col['rank'] = array(
			'type'    => 'int',
		);
		
		// arbitrary user-assigned rating, score, level, or value
		$this->col['rating'] = array(
			'type'    => 'int',
		);
		
		// serialized array of preferences for this node
		$this->col['prefs'] = array(
			'type'    => 'clob',
		);
		
		// -------------------------------------------------------------
		// 
		// KEYS AND INDEXES
		// 
		
		$this->idx = array(
			// composite unique index
			'full_name' => array(
				'type' => 'unique',
				'cols' => array('areas_name', 'name'),
			),
			// other indexes
			'areas_name' => 'normal',
			'name'      => 'normal',
			'tags'      => 'normal',
			'rank'      => 'normal',
			'rating'    => 'normal',
		);
	}
	
	public function fetchList($area_name, $page = null)
	{
		$where = array('area_name' => $area_name);
		$order = 'LOWER(name) ASC';
		return parent::select('all', $where, $order, $page);
	}
}
?>