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
		$this->col['edit_id'] = array(
			'type'    => 'int',
			'primary' => true,
			'require' => true,
			'seqname' => 'edit_id',
		);
		
		// which part this edit belongs to
		$this->col['edit_part_id'] = array(
			'type'    => 'int',
			'require' => true,
		);
		
		// timestamp when edited
		$this->col['edit_ts'] = array(
			'type'    => 'timestamp',
			'require' => true,
		);
		
		// IP address of the editor
		$this->col['edit_ip_addr'] = array(
			'type'    => 'char',
			'size'    => 15,
			'require' => true,
			'default' => Solar::server('REMOTE_ADDR'),
			'valid'   => array(
				array('ipv4'),
			),
		);
		
		// username of the editor
		$this->col['edit_user_handle'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// arbitrary flag: moderate, spam, disable, etc
		$this->col['edit_flag'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// MIME type: text/plain, text/x-solar-wiki, etc
		$this->col['edit_mime'] = array(
			'type'    => 'varchar',
			'size'    => 64,
			'require' => true,
			'default' => 'text/plain',
		);
		
		// subject, title, filename, uri, etc
		$this->col['edit_subj'] = array(
			'type'    => 'varchar',
			'size'    => 255,
		);
		
		// summary or short description
		$this->col['edit_summ'] = array(
			'type'    => 'varchar',
			'size'    => 255,
		);
		
		// the actual content
		$this->col['edit_body'] = array(
			'type'    => 'clob',
		);
		
		// serialized array of preferences for this edit
		$this->col['edit_prefs'] = array(
			'type'    => 'clob',
		);
		
		
		// -------------------------------------------------------------
		// 
		// KEYS AND INDEXES
		// 
		
		$this->idx = array(
			'edit_id'      => 'unique',
			'edit_part_id' => 'normal',
			'edit_ts'      => 'normal',
			'edit_flag'    => 'normal',
			'edit_subj'    => 'normal',
			'edit_summ'    => 'normal',
		);
	}
	
	public function fetchItem($edit_id)
	{
		$where = array('edit_id' => $edit_id);
		return parent::select('row', $where);
	}
}
?>