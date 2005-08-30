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
	
	protected setup()
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
		$this->column('edit_id', 'int');
		$this->require('edit_id');
		$this->sequence('edit_id');
		
		// which part this edit belongs to
		$this->column('edit_part_id', 'int');
		$this->require('edit_part_id');
		
		// timestamp when edited
		$this->column('edit_ts', 'timestamp');
		$this->require('edit_ts');
		
		// IP address of the editor
		$this->column('edit_ip_addr', 'char', 15);
		$this->require('edit_ip_addr');
		$this->validate('edit_ip_addr', 'ipv4');
		
		// username of the editor
		$this->column('edit_user_handle', 'varchar', 32);
		
		// arbitrary flag: moderate, spam, disable, etc
		$this->column('edit_flag', 'varchar', 32);
		
		// MIME type: text/plain, text/x-solar-wiki, etc
		$this->column('edit_mime', 'varchar', 64);
		$this->require('edit_mime');
		$this->default('edit_mime', 'literal', 'text/plain');
		
		// subject, title, filename, uri, etc
		$this->column('edit_subj', 'varchar', 255);
		
		// summary or short description
		$this->column('edit_summ', 'varchar', 255);
		
		// the actual content
		$this->column('edit_body', 'clob');
		
		// serialized array of preferences for this edit
		$this->column('edit_prefs', 'clob');
		
		
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
}
?>