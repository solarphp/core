<?php

/**
* 
* Application component module for a public shared bookmark list.
* 
* @category Solar
* 
* @package Solar_Cell
* 
* @subpackage Solar_Cell_Bookmarks
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id: Talk.php 66 2005-03-25 17:29:54Z pmjones $
* 
*/

/**
* Have the Entity class available for extension.
*/
Solar::autoload('Solar_Sql_Entity');


/**
* 
* Application component module for a public shared bookmark list.
* 
* @category Solar
* 
* @package Solar_Cell
* 
* @subpackage Solar_Cell_Bookmarks
* 
*/

class Solar_Cell_Tags extends Solar_Sql_Entity {
	
	/**
	* 
	* Additional config keys and values.
	* 
	*/
	
	public $config = array(
		'locale'         => 'Solar/Cell/Bookmarks/Locale/',
	);
	
	
	/**
	* 
	* Initialize the custom schema.
	* 
	* @access protected
	* 
	* @return void
	* 
	*/
	
	protected function getSchema()
	{
		// -------------------------------------------------------------
		// 
		// table name
		// 
		
		$schema['tbl'] = 'sc_bookmarks';
		
		
		// -------------------------------------------------------------
		// 
		// columns
		// 
		
		// sequential id
		$schema['col']['id'] = array(
			'type'     => 'int',
			'sequence' => 'sc_bookmarks',
			'primary'  => true,
			'require'  => true,
		);
			
		// user_id who owns this bookmark
		$schema['col']['user_id'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// timestamp when first added
		$schema['col']['ts_new'] = array(
			'type'    => 'timestamp',
			'require' => true,
		);
		
		// timestamp when last modified
		$schema['col']['ts_mod'] = array(
			'type'    => 'timestamp',
			'require' => true,
		);
		
		// the uniform resource indicator (http://example.com/whatever)
		$schema['col']['uri'] = array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true,
			'validate' => array(
				array(
					'uri',
					$this->locale('VALID_URI'),
				)
			)
		);
		
		// short title for the uri
		$schema['col']['title'] = array(
			'type'     => 'varchar',
			'size'     => 255,
			'require'  => true,
		);
		
		// longer description for the uri
		$schema['col']['descr'] = array(
			'type'     => 'varchar',
			'size'     => 255,
			'require'  => false,
		);
		
		
		// -------------------------------------------------------------
		// 
		// indexes
		// 
		
		$schema['idx'] = array(
			'id'      => 'unique',
			'user_id' => 'normal',
			'uri'     => 'normal',
		);
		
		
		// -------------------------------------------------------------
		// 
		// queries
		// 
		
		// list of bookmarks with tags
		$schema['qry']['list'] = array(
			'select' => '*, sc_tags.tags AS tags',
			'join'   => "sc_tags ON sc_tags.tbl = 'sc_bookmarks' AND sc_tags.tbl_id = sc_bookmarks.id",
			'order'  => 'ts_new DESC',
			'fetch'  => 'All'
		);
		
		// one bookmark item; same as list, just with different fetch
		$schema['qry']['item'] = array(
			'select' => '*, sc_tags.tags AS tags',
			'join'   => "sc_tags ON sc_tags.tbl = 'sc_bookmarks' AND sc_tags.tbl_id = sc_bookmarks.id",
			'where'  => 'id = :id',
			'fetch'  => 'Row'
		);
		
		return $schema;
	}
	
	public function fetchItem($id)
	{
		return $this->selectFetch('item', array('id' => $id));
	}
	
	
	public function fetchUser($user_id, $order = null, $page = null)
	{
		$where = 'user_id = ' . $this->quote($user_id);
		return $this->selectFetch('list', $where, $order, $page);
	}
	
	public function fetchTags($tags, $user_id = null, $order = null, $page = null)
	{
		// build a base where clause ...
		if ($user_id) {
			// ... to find a given user
			$where = 'user_id = ' . $this->quote('user_id');
		} else {
			// ... for all users
			$where = '1=1';
		}
		
		// convert $tags to array
		if (! is_array($tags)) {
			$tags = explode(' ', trim($tags));
		}
		
		// finish the where clause with tags ANDed together
		$tmp = array();
		foreach ($tags as $tag) {
			$tmp[] = "tags LIKE '% $tag %'";
		}
		$where .= ' AND ' . implode(' AND ', $tmp);
		
		// done!
		return $this->selectFetch('list', $where, $order, $page);
	}
	
	
	protected function preInsert(&$data)
	{
		if (isset($data['tags'])) {
			$data['tags'] = $this->fixTags($data['tags']);
		}
		
		$now = $this->timestamp();
		$data['ts_new'] = $now;
		$data['ts_mod'] = $now;
	}
	
	protected function preUpdate(&$data)
	{
		if (isset($data['tags'])) {
			$data['tags'] = $this->fixTags($data['tags']);
		}
		
		$data['ts_mod'] = $this->timestamp();
	}
	
	protected function fixTags($tags)
	{
		// trim, then convert to array for easy processing
		$data['tags'] = trim($data['tags']);
		$tmp = explode(' ', $data['tags']);
		
		// make sure each tag is unique (no double-entries)
		$tmp = array_unique($tmp);
		
		// convert back to text, with a space in front and behind
		$data['tags'] = ' ' . implode(' ', $tmp) . ' ';
		
		// remove all extra spaces, and done.
		$data['tags'] = preg_replace('/[ ]{2,}/', ' ', $data['tags']);
		return $tags;
	}
}
?>