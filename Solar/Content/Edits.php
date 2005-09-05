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

class Solar_Content_Edits extends Solar_Sql_Table {
	
	
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
		
		// unique ID for this edit
		$this->colDefine('edit_id', 'int');
		$this->colRequire('edit_id');
		$this->colSequence('edit_id');
		
		// which part this edit belongs to
		$this->colDefine('edit_part_id', 'int');
		$this->colRequire('edit_part_id');
		
		// timestamp when edited
		$this->colDefine('edit_ts', 'timestamp');
		$this->colRequire('edit_ts');
		
		// IP address of the editor
		$this->colDefine('edit_ip_addr', 'char', 15);
		$this->colRequire('edit_ip_addr');
		$this->colValid('edit_ip_addr', 'ipv4');
		
		// username of the editor
		$this->colDefine('edit_user_handle', 'varchar', 32);
		
		// arbitrary flag: moderate, spam, disable, etc
		$this->colDefine('edit_flag', 'varchar', 32);
		
		// MIME type: text/plain, text/x-solar-wiki, etc
		$this->colDefine('edit_mime', 'varchar', 64);
		$this->colRequire('edit_mime');
		$this->colDefault('edit_mime', 'literal', 'text/plain');
		
		// subject, title, filename, uri, etc
		$this->colDefine('edit_subj', 'varchar', 255);
		
		// summary or short description
		$this->colDefine('edit_summ', 'varchar', 255);
		
		// the actual content
		$this->colDefine('edit_body', 'clob');
		
		// serialized array of preferences for this edit
		$this->colDefine('edit_prefs', 'clob');
		
		
		// -------------------------------------------------------------
		// 
		// KEYS AND INDEXES
		// 
		
		$this->primary('edit_id');
		$this->unique('edit_id');
		$this->index('edit_part_id');
		$this->index('edit_ts');
		$this->index('edit_flag');
		$this->index('edit_subj');
		$this->index('edit_summ');
		
	}
	
	public function fetchItem($edit_id)
	{
		$where = array('edit_id' => $edit_id);
		$order = null;
		return parent::fetchItem($where, $order);
	}
}
?>