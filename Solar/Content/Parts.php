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

class Solar_Content_Parts extends Solar_Base {
	
	public $table;
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->table = Solar::object('Solar_Content_Parts_Table');
	}
	
	public function fetchList($area, $node, $types = null,
		$page = null)
	{
		$where = array(
			'areas_name' => $area,
			'nodes_name' => $node
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
			'LOWER(areas_name)',
			'LOWER(nodes_name)',
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