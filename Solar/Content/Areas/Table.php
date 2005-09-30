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
* @version $Id: Areas.php 527 2005-09-28 14:57:17Z pmjones $
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

class Solar_Content_Areas_Table extends Solar_Sql_Table {
	
	
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
		);
		
		// the user who owns this area
		$this->col['owner_handle'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// freeform area "subject" or title
		$this->col['subj'] = array(
			'type'    => 'varchar',
			'size'    => 255,
		);
		
		// serialized preferences
		$this->col['prefs'] = array(
			'type'    => 'clob',
		);
		
		
		// keys and indexes
		$this->idx = array(
			'name'         => 'unique',
			'owner_handle' => 'normal',
		);
	}
}
?>