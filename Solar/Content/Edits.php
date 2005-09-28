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
		$this->col['id'] = array(
			'type'    => 'int',
			'primary' => true,
			'require' => true,
			'seqname' => 'id',
		);
		
		// which part this edit belongs to
		$this->col['parts_id'] = array(
			'type'    => 'int',
			'require' => true,
		);
		
		// timestamp when edited
		$this->col['ts'] = array(
			'type'    => 'timestamp',
			'require' => true,
		);
		
		// IP address of the editor
		$this->col['ip_addr'] = array(
			'type'    => 'char',
			'size'    => 15,
			'require' => true,
			'default' => Solar::server('REMOTE_ADDR'),
			'valid'   => array(
				array('ipv4'),
			),
		);
		
		// username of the editor
		$this->col['users_handle'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// arbitrary flag: moderate, spam, disable, etc
		$this->col['flag'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// MIME type: text/plain, text/x-solar-wiki, etc
		$this->col['mime'] = array(
			'type'    => 'varchar',
			'size'    => 64,
			'require' => true,
			'default' => 'text/plain',
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
		
		// the actual content
		$this->col['body'] = array(
			'type'    => 'clob',
		);
		
		// serialized array of preferences for this edit
		$this->col['prefs'] = array(
			'type'    => 'clob',
		);
		
		
		// -------------------------------------------------------------
		// 
		// KEYS AND INDEXES
		// 
		
		$this->idx = array(
			'id'       => 'unique',
			'parts_id' => 'normal',
			'ts'       => 'normal',
			'flag'     => 'normal',
			'subj'     => 'normal',
			'summ'     => 'normal',
		);
	}
	
	public function fetchItem($id)
	{
		$where = array('id' => $id);
		return parent::select('row', $where);
	}
}
?>