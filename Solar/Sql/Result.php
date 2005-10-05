<?php

/**
* 
* Class for iterating through selected row results.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Sql
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
* Class for iterating through selected row results.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Sql
* 
*/

class Solar_Sql_Result extends Solar_Base {
	
	protected $config = array(
		'PDOStatement' => null,
	);
	
	/*
	public function __call($func, $args)
	{
		return call_user_func_array(
			array($this->config['PDOStatement'], $func),
			$args
		);
	}
	*/
	
	public function fetch($mode = PDO_FETCH_ASSOC)
	{
		// the fetched row data 
		$row = array();
		$orig = $this->config['PDOStatement']->fetch($mode);
		
		if (! $orig) {
			return false;
		}
		
		// if the name has __ in it, assume that the
		// left portion is the table name, and the
		// right portion is the column name. otherwise
		// it's just a column name.
		foreach ($orig as $key => $val) {
			$pos = strpos($key, '__');
			if ($pos) {
				$tbl = substr($key, 0, $pos);
				$col = substr($key, $pos+2);
				$row[$tbl][$col] = $val;
			} else {
				$row[$key] = $val;
			}
		}
		return $row;
	}
	
	public function fetchAll($mode = PDO_FETCH_ASSOC)
	{
		$data = array();
		while ($row = $this->fetch($mode)) {
			$data[] = $row;
		}
		return $data;
	}
}
?>