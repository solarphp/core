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
* @version $Id: Nodes.php 527 2005-09-28 14:57:17Z pmjones $
* 
*/

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

class Solar_Content_Nodes_Table extends Solar_Sql_Table {
	
	
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
		$this->col['area_id'] = array(
			'type'    => 'int',
			'require' => true,
			'valid'   => 'word',
		);
		
		// the user who owns this node
		$this->col['owner_handle'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// the node name (equivalent to a wiki-word)
		$this->col['name'] = array(
			'type'    => 'varchar',
			'size'    => 127,
			'require' => true,
			'valid'   => 'word',
		);
		
		// the node type
		$this->col['type'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// the node "subject" or title
		$this->col['subj'] = array(
			'type'    => 'varchar',
			'size'    => 255,
		);
		
		// summary description of the node
		$this->col['summ'] = array(
			'type'    => 'varchar',
			'size'    => 255,
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
			// composite unique index to ensure unique node names within
			// an area_id
			'unique_in_area' => array(
				'type' => 'unique',
				'cols' => array('area_id', 'name'),
			),
			// other indexes
			'area_id'      => 'normal',
			'name'         => 'normal',
			'owner_handle' => 'normal',
			'rank'         => 'normal',
			'rating'       => 'normal',
		);
	}
}
?>