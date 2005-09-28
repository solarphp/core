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

class Solar_Content_Areas extends Solar_Sql_Table {
	
	
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
		$this->name = 'areas';
		
		// the area name
		$this->col['name'] = array(
			'type'    => 'varchar',
			'size'    => 127,
			'require' => true,
			'valid'   => array('word'),
			'primary' => true,
		);
		
		// freeform area "subject" or title
		$this->col['subj'] = array(
			'type'    => 'varchar',
			'size'    => 255,
		);
		
		// the user who owns this area
		$this->col['users_handle'] = array(
			'type'    => 'varchar',
			'size'    => 32,
			'valid'   => array('word'),
		);
		
		// serialized preferences
		$this->col['prefs'] = array(
			'type'    => 'clob',
		);
		
		
		// keys and indexes
		$this->idx = array(
			'name'        => 'unique',
			'users_handle' => 'normal',
		);
	}
	
	public function fetchList($page = null)
	{
		$type = 'all';
		$where = null;
		$order = 'LOWER(name) ASC';
		return $this->select($type, $where, $order, $page);
	}
	
	public function fetchItem($name)
	{
		$type = 'row';
		$where = array('name' => $name);
		return $this->select($type, $where);
	}
}
?>