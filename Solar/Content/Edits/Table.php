<?php

/**
* 
* Prior versions (edits) of a part.
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
* @version $Id: Edits.php 527 2005-09-28 14:57:17Z pmjones $
* 
*/

/**
* 
* Prior versions (edits) of a part.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Content
* 
*/

class Solar_Content_Edits_Table extends Solar_Sql_Table {
	
	
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
		// -------------------------------------------------------------
		// 
		// TABLE
		// 
		
		$this->name = 'edits';
		
		
		// -------------------------------------------------------------
		// 
		// COLUMNS
		// 
		
		// which part this edit belongs to
		$this->col['part_id'] = array(
			'type'    => 'int',
			'require' => true,
		);
		
		// username of the editor
		$this->col['editor_handle'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// ip address of the editor
		$this->col['editor_ipaddr'] = array(
			'type'    => 'char',
			'size'    => 15,
		);
		
		// arbitrary flag: moderate, spam, disable, etc
		$this->col['flag'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// email related to this part
		$this->col['email'] = array(
			'type'    => 'varchar',
			'size'    => 255,
		);
		
		// uri related to this part
		$this->col['uri'] = array(
			'type'    => 'varchar',
			'size'    => 255,
		);
		
		// subject, title, filename, uri, etc
		$this->col['subj'] = array(
			'type'    => 'varchar',
			'size'    => 255,
		);
		
		// summary or short description
		$this->col['summ'] = array(
			'type'    => 'varchar',
			'size'    => 255,
		);
		
		// MIME type: text/plain, text/x-solar-wiki, etc
		$this->col['mime'] = array(
			'type'    => 'varchar',
			'size'    => 64,
			'require' => true,
		);
		
		// the actual content
		$this->col['body'] = array(
			'type'    => 'clob',
		);
		
		
		// -------------------------------------------------------------
		// 
		// KEYS AND INDEXES
		// 
		
		$this->idx = array(
			'part_id' => 'normal',
		);
	}
}
?>