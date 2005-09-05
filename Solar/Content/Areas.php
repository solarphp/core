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
		$this->colDefine('area_name', 'varchar', 127);
		$this->colRequire('area_name');
		$this->colValid('area_name', 'word');
		
		// freeform area "subject" or title
		$this->colDefine('area_subj', 'varchar', 255);
		
		// the user who owns this area
		$this->colDefine('area_user_handle', 'varchar', 32);
		$this->colValid('area_user_handle', 'word');
		
		// serialized preferences
		$this->colDefine('area_prefs', 'clob');
		
		// keys and indexes
		$this->primary('area_name');
		$this->unique('area_name');
		$this->index('area_user_handle');
	}
	
	public function fetchList($page = null)
	{
		$where = null;
		$order = 'LOWER(area_name) ASC';
		return parent::fetchList($where, $order, $page);
	}
	
	public function fetchItem($area_name)
	{
		$where = array('area_name' => $area_name);
		$order = null;
		return parent::fetchItem($where, $order);
	}
}
?>