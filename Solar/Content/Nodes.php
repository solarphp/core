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
*/

class Solar_Content_Nodes extends Solar_Base {

	public $table;
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->table = Solar::object('Solar_Content_Nodes_Table');
	}
	
	public function exists($area, $node)
	{
		// allow id or name for area
		if (is_numeric($area)) {
			$areas_col = 'areas.id';
		} else {
			$areas_col = 'areas.name';
		}
		
		// allow id or name for node
		if (is_numeric($node)) {
			$nodes_col = 'nodes.id';
		} else {
			$nodes_col = 'nodes.name';
		}
		
		// create a select tool
		$select = Solar::object('Solar_Sql_Select');
		
		// which tables?
		$select->from('nodes');
		$select->join('areas', 'nodes.area_id = areas.id');
		
		// filter by area
		$select->where("$areas_col = :area_val");
		$select->bind('area_val', $area);
		
		// filter by node
		$select->where("$nodes_col = :node_val");
		$select->bind('node_val', $node);
		
		// get a count
		$result = $select->countPages();
		
		if (Solar::isError($result)) {
			return $result;
		} else {
			// successful select, just check the
			// count of how many names showed up
			// and cast as boolean.
			return (bool) $result['count'];
		}
	}
	
	public function fetchList($where = null, $order = null, $page = null)
	{
		if (is_null($order)) {
			$order = array('rank ASC', 'LOWER(name) ASC');
		}
		
		return $this->table->select('all', $where, $order, $page);
	}
	
	public function fetchItem($area, $node)
	{
		// allow id or name for area
		if (is_numeric($area)) {
			// no need for a join in this case
			$areas_col = 'nodes.area_id';
		} else {
			$areas_col = 'areas.name';
		}
		
		// allow id or name for node
		if (is_numeric($node)) {
			$nodes_col = 'nodes.id';
		} else {
			$nodes_col = 'nodes.name';
		}
		
		// create a select tool
		$select = Solar::object('Solar_Sql_Select');
		
		// select all cols from this table
		$select->from($this->table, '*');
		
		// if area was by name (i.e., not by numeric ID),
		// join the areas table, but no columns selected from it.
		if (! is_numeric($area)) {
			$select->join('areas', 'nodes.area_id = areas.id');
		}
		
		// filter by area
		$select->where("$areas_col = :area_val");
		$select->bind('area_val', $area);
		
		// filter by node
		$select->where("$nodes_col = :node_val");
		$select->bind('node_val', $node);
		
		// return the results
		return $select->fetch('row');
	}
}
?>