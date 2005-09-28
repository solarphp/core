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
	
	protected function setup()
	{
		// the table name
		$this->name = 'parts';
		
		// -------------------------------------------------------------
		// 
		// COLUMNS
		// 
		
		// unique ID for this part
		$this->col['id'] = array(
			'type'    => 'int',
			'require' => true,
			'primary' => true,
			'seqname' => 'id',
		);
		
		// the area in which this part belongs
		$this->col['area_name'] = array(
			'type'    => 'varchar',
			'size'    => 127,
			'require' => true,
			'valid'   => array(
				array('word'),
			),
		);
		
		// the node in which this part belongs
		$this->col['node_name'] = array(
			'type'    => 'varchar',
			'size'    => 127,
			'require' => true,
			'valid'   => array(
				array('word'),
			),
		);
		
		// the arbitrary part type: wiki, blog, news, comment, trackback, etc.
		$this->col['type'] = array(
			'type'    => 'varchar',
			'size'    => 32,
			'require' => true,
			'valid'   => array(
				array('word'),
			),
		);
		
		// the locale for this part
		$this->col['locale'] = array(
			'type'    => 'char',
			'size'    => 5,
			'require' => true,
			'default' => 'en_US',
			'valid'   => array(
				array('locale'),
			),
		);
		
		// the most-recent edit ID number
		$this->col['edit_id'] = array(
			'type'    => 'int',
			'require' => true,
		);
		
		// the user who owns this part
		$this->col['user_handle'] = array(
			'type'    => 'varchar',
			'size'    => 32,
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
			'id'        => 'unique',
			'area_name' => 'normal',
			'node_name' => 'normal',
			'type'      => 'normal',
			'locale'    => 'normal',
			'edit_id'   => 'normal',
			'rank'      => 'normal',
			'rating'    => 'normal',
		);
	}
	
	
	public function fetchList($area_name, $node_name, $types = null,
		$page = null)
	{
		$where = array(
			'area_name' => $area_name,
			'node_name' => $node_name
		);
		
		// add the types to filter for (null means get all types)
		if (! is_null($types)) {
			// force to an array and quote every value
			settype($types, 'array');
			$types = $this->sql->quote($types);
			// add to the WHERE filter
			$where[] = 'IN (' . implode(', ', $types) . ')';
		}
		
		// order is by area, node, type, rank, and timestamp
		$order = array(
			'LOWER(area_name)',
			'LOWER(node_name)',
			'LOWER(type)',
			'rank',
			'ts'
		);
		
		// done, return the list
		return parent::select('all', $where, $order, $page);
	}
	
	public function fetchItem($id)
	{
		$where = array('id' => $id);
		return parent::select('row', $where);
	}
}
?>