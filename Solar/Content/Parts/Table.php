<?php

/**
* 
* Individual parts of content within a node: wiki page, comment, etc.
* 
* Each part represents its own most-recent version; older versions are
* archived in the 'edits' table.
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
* @version $Id: Parts.php 527 2005-09-28 14:57:17Z pmjones $
* 
*/

/**
* 
* Individual parts of content within a node: wiki page, comment, etc.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Content
* 
*/

class Solar_Content_Parts_Table extends Solar_Sql_Table {
	
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
		
		// the node in which this part belongs
		$this->col['node_id'] = array(
			'type'    => 'int',
			'require' => true,
		);
		
		// username of the part owner
		$this->col['owner_handle'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// username of the most-recent editor
		$this->col['editor_handle'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// ip address of the most-recent editor
		$this->col['editor_ipaddr'] = array(
			'type'    => 'char',
			'size'    => 15,
			'require' => true,
			'default' => Solar::server('REMOTE_ADDR'),
			'valid'   => 'ipv4',
		);
		
		// the locale for this part
		$this->col['locale'] = array(
			'type'    => 'char',
			'size'    => 5,
			'require' => true,
			'default' => 'en_US',
			'valid'   => 'locale',
		);
		
		// arbitrary list-order, sequence, or ranking
		$this->col['rank'] = array(
			'type'    => 'int',
		);
		
		// arbitrary user-assigned rating, score, level, or value
		$this->col['rating'] = array(
			'type'    => 'int',
		);
		
		// tags on this part
		$this->col['tags'] = array(
			'type'    => 'varchar',
			'size'    => 255,
			'valid'   => array(
				array('regex', '/^[\w ]$+/'),
			),
		);
		
		// the part type: wiki, blog, news, comment, trackback, etc.
		$this->col['type'] = array(
			'type'    => 'varchar',
			'size'    => 32,
			'require' => true,
			'valid'   => 'word',
		);
		
		// email related to this part
		$this->col['email'] = array(
			'type'    => 'varchar',
			'size'    => 255,
			'valid'   => 'email',
		);
		
		// uri related to this part
		$this->col['uri'] = array(
			'type'    => 'varchar',
			'size'    => 255,
			'valid'   => 'uri',
		);
		
		// subject, title, filename, etc for this part
		$this->col['subj'] = array(
			'type'    => 'varchar',
			'size'    => 255,
		);
		
		// summary or short description of the body
		$this->col['summ'] = array(
			'type'    => 'varchar',
			'size'    => 255,
		);
		
		// MIME type of the body: text/plain, text/x-solar-wiki, etc
		$this->col['mime'] = array(
			'type'    => 'varchar',
			'size'    => 64,
			'require' => true,
			'valid'   => 'mimeType',
			'default' => 'text/plain',
		);
		
		// the actual content
		$this->col['body'] = array(
			'type'    => 'clob',
		);
		
		
		// serialized array of preferences for this part
		$this->col['prefs'] = array(
			'type'    => 'clob',
		);
		
		
		// -------------------------------------------------------------
		// 
		// KEYS AND INDEXES
		// 
		
		$this->idx = array(
			'node_id'      => 'normal',
			'owner_handle' => 'normal',
			'locale'       => 'normal',
			'rank'         => 'normal',
			'rating'       => 'normal',
			'type'         => 'normal',
			'email'        => 'normal',
			'uri'          => 'normal',
			'tags'         => 'normal',
			'subj'         => 'normal',
			'summ'         => 'normal',
		);
	}
}
?>