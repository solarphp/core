<?php

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

class Solar_Content_Areas extends Solar_Base {
	
	public $table;
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->table = Solar::object('Solar_Content_Areas_Table');
	}
	
	public function exists($area)
	{
		// create a select tool
		$select = Solar::object('Solar_Sql_Select');
		
		// select from this table name (note that we don't
		// just pass the object, although we can, becuase
		// we don't want to auto-add columns).
		$select->from('areas');
		
		// by ID or by name?
		if (is_numeric($area)) {
			$col = 'id';
		} else {
			$col = 'name';
		}
		
		// filter by the column name and bind a value
		$select->where("$col = :value");
		$select->bind('value', trim($area));
		
		// get a result count
		$result = $select->countPages();
		if (Solar::isError($result)) {
			// failure
			return $result;
		} else {
			// success. just check the count of how many names showed up
			// and cast as boolean.
			return (bool) $result['count'];
		}
	}
	
	public function fetchList($where = null, $order = null,	$page = null)
	{
		if (is_null($order)) {
			$order = 'LOWER(name) ASC';
		}
		return $this->table->select('all', $where, $order, $page);
	}
	
	public function fetchItem($area)
	{
		if (is_numeric($area)) {
			$col = 'id';
		} else {
			$col = 'name';
		}
		
		$type = 'row';
		$where = array($col => trim($area));
		return $this->table->select('row', $where);
	}
}
?>