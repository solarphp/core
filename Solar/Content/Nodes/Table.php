<?php

/**
* 
* Nodes of content within an area.
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
* @version $Id: Nodes.php 527 2005-09-28 14:57:17Z pmjones $
* 
*/

/**
* 
* Nodes of content within an area.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Content
* 
*/

class Solar_Content_Nodes_Table extends Solar_Sql_Table {
	
	
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
		$this->name = 'nodes';
		
		// -------------------------------------------------------------
		// 
		// COLUMNS
		// 
		
		// the area in which this node belongs
		$this->col['area_id'] = array(
			'type'    => 'int',
			'require' => true,
			'valid'   => 'word',
		);
		
		// the node name (equivalent to a wiki-word)
		$this->col['name'] = array(
			'type'    => 'varchar',
			'size'    => 127,
			'valid'   => 'word',
		);
		
		// is this node part of another node?
		$this->col['part_of'] = array(
			'type'    => 'int',
			'require' => true,
			'default' => 0,
		);
		
		// username of the node owner
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
			'default' => Solar::server('REMOTE_ADDR'),
			'valid'   => 'ipv4',
		);
		
		// the node type (bookmark, wiki, blog, comment, trackback, etc)
		$this->col['type'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// the locale for this part
		$this->col['locale'] = array(
			'type'    => 'char',
			'size'    => 5,
			'default' => 'en_US',
			'valid'   => 'locale',
		);
		
		// tags on this node
		$this->col['tags'] = array(
			'type'    => 'varchar',
			'size'    => 255,
			'valid'   => array(
				array('regex', '/^[\w ]$+/'),
			),
		);
		
		// arbitrary list-order, sequence, or ranking
		$this->col['rank'] = array(
			'type'    => 'int',
		);
		
		// arbitrary user-assigned rating, score, level, or value
		$this->col['rating'] = array(
			'type'    => 'int',
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
		
		// the node "subject" or title
		$this->col['subj'] = array(
			'type'    => 'varchar',
			'size'    => 255,
		);
		
		// summary description of the node
		$this->col['summ'] = array(
			'type'    => 'varchar',
			'size'    => 255,
		);
		
		// mime type of the body
		$this->col['mime'] = array(
			'type'    => 'varchar',
			'size'    => 64,
			'default' => 'text/plain',
			'valid'   => 'mimeType',
		);
		
		// the actual node content
		$this->col['body'] = array(
			'type'    => 'clob',
		);
		
		// serialized array of preferences for this node
		$this->col['prefs'] = array(
			'type'    => 'clob',
		);
		
		// -------------------------------------------------------------
		// 
		// KEYS AND INDEXES
		// 
		
		$this->idx = array(
			// composite unique index to ensure unique node names within
			// an area_id
			'unique_in_area' => array(
				'type' => 'unique',
				'cols' => array('area_id', 'name'),
			),
			// other indexes
			'area_id'      => 'normal',
			'name'         => 'normal',
			'part_of'      => 'normal',
			'owner_handle' => 'normal',
			'type'         => 'normal',
			'locale'       => 'normal',
			'tags'         => 'normal',
			'rank'         => 'normal',
			'rating'       => 'normal',
			'uri'          => 'normal',
			'email'        => 'normal',
		);
	}
	
	public function insert($data)
	{
		// although the parent would increment the ID for us automatically,
		// we do so manually here, because we may need it for a name (i.e.,
		// if the name is blank or not set). 
		if (empty($data['id'])) {
			$data['id'] = $this->increment('id');
		}
		
		// make sure we have a unique name for the node (specifically,
		// a unique name in the area_id for this node).
		if (empty($data['name'])) {
			$data['name'] = $data['id'];
		}
		
		return parent::insert($data);
	}
}
?>