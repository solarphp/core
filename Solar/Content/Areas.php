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
* @version $Id:$
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
	
	protected setup()
	{
		// the table name
		$this->name = 'areas';
		
		// the area name
		$this->column('area_name', 'varchar', 127);
		$this->require('area_name');
		$this->validate('area_name', 'word');
		
		// freeform area "subject" or title
		$this->column('area_subj', 'varchar', 255);
		
		// the user who owns this area
		$this->column('area_user_handle', 'varchar', 32);
		$this->validate('area_user_handle', 'word');
		
		// serialized preferences
		$this->column('area_prefs', 'clob');
		
		// keys and indexes
		$this->primary('area_name');
		$this->unique('area_name');
		$this->index('area_user_handle');
	}
}
?>