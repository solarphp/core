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
		$this->colDefine('part_id', 'int');
		$this->colRequire('part_id');
		$this->colSequence('part_id');
		
		// the area in which this part belongs
		$this->colDefine('part_area_name', 'varchar', 127);
		$this->colRequire('part_area_name');
		$this->colValid('part_area_name', 'word');
		
		// the node in which this part belongs
		$this->colDefine('part_node_name', 'varchar', 127);
		$this->colRequire('part_node_name');
		$this->colValid('part_node_name', 'word');
		
		// the arbitrary part type: wiki, blog, news, comment, trackback, etc.
		$this->colDefine('part_type', 'varchar', 32);
		$this->colRequire('part_type');
		$this->colValid('part_type', 'word');
		
		// the locale for this part
		$this->colDefine('part_locale', 'char', 5);
		$this->colRequire('part_locale');
		$this->colDefault('part_locale', 'literal', 'en_US');
		$this->colValid('part_locale', 'locale');
		
		// the most-recent edit ID number
		$this->colDefine('part_edit_id', 'int');
		$this->colRequire('part_edit_id');
		
		// the user who owns this part
		$this->colDefine('part_user_handle', 'varchar', 32);
		$this->colValid('part_user_handle', 'word');
		
		// arbitrary list-order, sequence, or ranking
		$this->colDefine('part_rank', 'int');
		
		// arbitrary user-assigned rating, score, level, or value
		$this->colDefine('part_rating', 'int');
		
		// serialized array of preferences for this part
		$this->colDefine('part_prefs', 'clob');
		
		
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
	
	
	public function fetchList($area_name, $node_name, $types = null, $page = null)
	{
		$where[] = 'part_area_name = ' . $this->sql->quote($area_name);
		$where[] = 'part_node_name = ' . $this->sql->quote($node_name);
		
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
			'LOWER(part_area_name)',
			'LOWER(part_node_name)',
			'LOWER(part_type)',
			'part_rank',
			'part_ts'
		);
		
		// done, return the list
		return parent::fetchList($where, $order, $page);
	}
	
	public function fetchItem($part_id)
	{
		$where = array('part_id');
		$order = null;
		return parent::fetchItem($where, $order);
	}
}
?>