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
* @version $Id$
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

class Solar_Cell_Bookmarks extends Solar_Sql_Entity {
	
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
		
		// tags (a la del.icio.us)
		$schema['col']['tags'] = array(
			'type'     => 'varchar',
			'size'     => 255,
			'require'  => false,
			'validate' => array(
				array(
					'regex',
					$this->locale('VALID_TAGS'),
					'/[A-Za-z0-9_ ]*/'
				)
			),
		);
		
		
		// -------------------------------------------------------------
		// 
		// indexes
		// 
		
		$schema['idx'] = array(
			'id'      => 'unique',
			'user_id' => 'normal',
			'uri'     => 'normal',
			'tags'    => 'normal',
		);
		
		
		// -------------------------------------------------------------
		// 
		// queries
		// 
		
		// list of bookmarks with tags
		$schema['qry']['list'] = array(
			'select' => '*',
			'order'  => 'ts_new DESC',
			'fetch'  => 'All'
		);
		
		// one bookmark item; same as list, just with different fetch
		$schema['qry']['item'] = array(
			'select' => '*',
			'where'  => 'id = :id',
			'fetch'  => 'Row'
		);
		
		return $schema;
	}
	
	
	/**
	* 
	* Fetch a bookmark item by ID.
	* 
	* @access public
	* 
	* @param int $id The bookmark ID.
	* 
	* @return array The bookmark item array.
	* 
	*/
	
	public function item($id)
	{
		return $this->selectFetch('item', array('id' => $id));
	}
	
	
	/**
	* 
	* Fetch a a generic list of bookmarks.
	* 
	*/
	
	public function list($where = null, $order = null, $page = null)
	{
		$where = 'user_id = ' . $this->quote($user_id);
		return $this->selectFetch('list', $where, $order, $page);
	}
	
	
	/**
	* 
	* Fetch all the bookmarks for a specific user_id.
	* 
	* @access public
	* 
	* @param string $user_id The user_id to look up.
	* 
	* @param string $order A custom ORDER clause.
	* 
	* @param int $page The page number to return.
	* 
	* @return array The list of bookmarks.
	* 
	*/
	
	public function user($user_id, $order = null, $page = null)
	{
		$where = 'user_id = ' . $this->quote($user_id);
		return $this->list($where, $order, $page);
	}
	
	
	/**
	* 
	* Fetch all the bookmarks with a specific tagset.
	* 
	* Optionally, you can limit to a specific username as well.
	* 
	* @access public
	* 
	* @param string|array $tags The tags to look up; may be a space-separated
	* string of tags.  Will "AND" these for an intersection of tags.
	* 
	* @param string $user_id The optional user_id to look up; if null, will
	* look at all users.
	* 
	* @param string $order A custom ORDER clause.
	* 
	* @param int $page The page number to return.
	* 
	* @return array The list of bookmarks.
	* 
	*/
	
	public function tags($tags, $user_id = null, $order = null, $page = null)
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
			if (trim($tag) != '') {
				$tmp[] = "tags LIKE " . $this->quote('% $tag %');
			}
		}
		$where .= ' AND ' . implode(' AND ', $tmp);
		
		// done!
		return $this->list($where, $order, $page);
	}
	
	
	/**
	* 
	* Pre-insert data manipulation.
	* 
	*/
	
	protected function preInsert(&$data)
	{
		if (isset($data['tags'])) {
			$data['tags'] = $this->fixTags($data['tags']);
		}
		
		$now = $this->timestamp();
		$data['ts_new'] = $now;
		$data['ts_mod'] = $now;
	}
	
	
	/**
	* 
	* Pre-update data manipulation.
	* 
	*/
	
	protected function preUpdate(&$data)
	{
		if (isset($data['tags'])) {
			$data['tags'] = $this->fixTags($data['tags']);
		}
		
		$data['ts_mod'] = $this->timestamp();
	}
	
	
	/**
	* 
	* Fixes tag arrays and strings for the database.
	* 
	*/
	
	protected function fixTags($tags)
	{
		// convert to array from string?
		if (! is_array($tags)) {
			// trim all surrounging spaces (and extra spaces)
			$tags = trim($tags);
			$tags = preg_replace('/[ ]{2,}/', ' ', $tags);
			
			// convert to array for easy processing
			$tmp = explode(' ', $tags);
		}
		
		// make sure each tag is unique (no double-entries)
		$tmp = array_unique($tmp);
		
		// return as text, with a space in front and behind
		return ' ' . implode(' ', $tmp) . ' ';
	}
}
?>