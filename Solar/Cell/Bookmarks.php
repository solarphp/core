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
Solar::loadClass('Solar_Sql_Entity');


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
	
	// object for tag searches
	protected $tags;
	
	// count of records in last search
	public $count = 0;
	
	// number of pages in last search
	public $pages = 0;
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	*/
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->tags = Solar::object('Solar_Cell_Tags');
	}
	
	
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
		
		// arbitrary rank value (order, sequence, popularity, etc)
		$schema['col']['rank'] = array(
			'type'     => 'int',
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
			'rank'    => 'normal',
			'tags'    => 'normal',
		);
		
		
		// -------------------------------------------------------------
		// 
		// relationships
		// 
		
		$schema['rel']['search_tags'] = array(
			'table' => 'sc_tags',
			'on' => array(
				'rel' =>    "'sc_bookmarks'", // note this is a literal string
				'rel_id' => "sc_bookmarks.id",
			),
		);
		
		
		// -------------------------------------------------------------
		// 
		// queries
		// 
		
		// list of bookmarks
		$schema['qry']['list'] = array(
			'select' => '*',
			'order'  => 'ts_new DESC',
			'fetch'  => 'All'
		);
		
		// one bookmark
		$schema['qry']['item'] = array(
			'select' => '*',
			'where'  => 'id = :id',
			'fetch'  => 'Row'
		);
		
		/*
		// lookup by tags and optionally by user
		$schema['qry']['withTags'] = array(
			'select' => 'sc_bookmarks.*',
			'join'   => 'search_tags',
			'group'  => 'sc_bookmarks.id',
			'having' => 'COUNT(sc_bookmarks.id) = :count',
			'fetch'  => 'All'
		);
		*/
		
		// the list of tags from one user
		$schema['qry']['userTags'] = array(
			'select' => 'DISTINCT sc_tags.tag AS tag',
			'join'   => 'search_tags',
			'where'  => 'user_id = :user_id',
			'order'  => 'LOWER(tag)',
			'fetch'  => 'Col'
		);
		
		// -------------------------------------------------------------
		// 
		// forms
		// 
		
		// edit form
		$schema['frm']['edit'] = array(
			'id' => array('type' => 'hidden'),
			'title' => array(
				'attribs' => array('size' => '60'),
				'validate' => array(
					array('notBlank', $this->locale('ERR_TITLE')),
				)
			),
			'uri' => array(
				'attribs' => array('size' => '60'),
				'validate' => array(
					array('uri', $this->locale('ERR_URI')),
				),
			),
			'descr' => array(
				'attribs' => array('size' => '60'),
			),
			'tags' => array(
				'attribs' => array('size' => '60'),
				'validate' => array(
					array('regex', $this->locale('ERR_TAGS'), '/^[A-Za-z0-9_ ]*$/'),
				),
			),
			'rank' => array(
				'attribs' => array('size' => '5'),
				'validate' => array(
					array('integer',  $this->locale('ERR_RANK'), Solar_Valid::OR_BLANK),
				),
			),
		);
		
		// -------------------------------------------------------------
		// 
		// done!
		// 
		
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
	
	public function fetchItem($id)
	{
		$result = $this->selectFetch('item', array('id' => $id));
		if (! Solar::isError($result)) {
			$this->count = 1;
			$this->pages = 1;
		}
		return $result;
	}
	
	
	/**
	* 
	* Fetch a a generic list of bookmarks.
	* 
	*/
	
	public function fetchList($where = null, $order = null, $page = null)
	{
		$result = $this->selectFetch('list', $where, null, $order, $page);
		if (! Solar::isError($result)) {
			$tmp = $this->countPages('list', $where);
			$this->count = $tmp['count'];
			$this->pages = $tmp['pages'];
		}
		return $result;
	}
	
	
	/**
	* 
	* See if a user_id already has a uri in their bookmarks.
	* 
	* @access public
	* 
	* @param string $user_id The user_id to look up.
	* 
	* @param string $uri The URI to look up.
	*
	* @return mixed Boolean false if the user_id does not already
	* have that URI, or a record ID number if he does have it.
	* 
	*/
	
	public function userHasUri($user_id, $uri)
	{
		$where = 'user_id = ' . $this->quote($user_id) . 
			' AND uri = ' . $this->quote($uri);
		
		$result = $this->selectFetch('list', $where);
		
		if (Solar::isError($result)) {
			return $result;
		} elseif ($result) {
			return $result[0]['id'];
		} else {
			return false;
		}
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
	
	public function forUser($user_id, $order = null, $page = null)
	{
		$where = 'user_id = ' . $this->quote($user_id);
		return $this->fetchList($where, $order, $page);
	}
	
	
	/**
	* 
	* Fetch all the tags used by a user_id.
	* 
	* @access public
	* 
	* @param string $user_id The user_id to look up.
	* 
	* @return array The list of tags from that user.
	* 
	*/
	
	public function userTags($user_id)
	{
		return $this->selectFetch('userTags', array('user_id' => $user_id));
	}
	
	
	/**
	* 
	* Fetch all the bookmarks with a specific tags.
	* 
	* Optionally, you can limit to a specific username as well.
	* 
	* Technically, the method searches the sc_bookmarks_tags table, which joins
	* itself back to this table (sc_bookmarks) for the "real" info.
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
	
	public function withTags($tags, $user_id = null, $order = null, $page = null)
	{
		// build a where clause to find a given user?
		if ($user_id) {
			// ... find a given user
			$where = 'user_id = ' . $this->quote($user_id);
		} else {
			// ... find for all users
			$where = null;
		}
		
		// make sure the rows-per-page matches
		$prev_rpp = $this->tags->config['rows_per_page'];
		$this->tags->config['rows_per_page'] = $this->config['rows_per_page'];
		
		// get the results
		$having = null;
		$result = $this->tags->relatedFetch('sc_bookmarks', $tags, '*', $where,
			$having, $order, $page);
		
		// set up the count and pages
		if (! Solar::isError($result)) {
			$tmp = $this->tags->relatedCountPages($where, $having);
			$this->count = $tmp['count'];
			$this->pages = $tmp['pages'];
		}
		
		// done!
		$this->tags->config['rows_per_page'] = $prev_rpp;
		return $result;
	}
	
	
	/**
	* 
	* Custom pre-insert processing to set timestamps and fix tags.
	* 
	* @access protected
	* 
	* @param array &$data An associative array of data to be inserted, in
	* the format (field => value).
	* 
	* @return mixed Void on success, Solar_Error object on failure.
	* 
	*/
	
	protected function preInsert(&$data)
	{
		if (isset($data['tags'])) {
			$data['tags'] = $this->tags->fixString($data['tags']);
		}
		
		$now = $this->timestamp();
		$data['ts_new'] = $now;
		$data['ts_mod'] = $now;
	}
	
	
	/**
	* 
	* Custom post-insert processing to refresh the tags table.
	* 
	* @access protected
	* 
	* @param array &$data An associative array of data to be inserted, in
	* the format (field => value).
	* 
	* @return mixed Void on success, Solar_Error object on failure.
	* 
	*/
	
	protected function postInsert(&$data)
	{
		if (isset($data['tags'])) {
			return $this->tags->refresh(
				'sc_bookmarks',
				$data['id'],
				$data['tags']
			);
		}
	}
	
	
	/**
	* 
	* Updates one bookmark at a time by its ID.
	* 
	* @access public
	* 
	* @param array $data An associative array of data to be updated, in
	* the format (field => value).
	* 
	* @param int $id The bookmark ID to update.
	* 
	* @return mixed Boolean true on success, Solar_Error object on failure.
	* 
	*/
	
	public function update($data, $id)
	{
		$where = 'id = ' . $this->quote($id);
		return parent::update($data, $where);
	}
	
	
	/**
	* 
	* Custom pre-update processing to set timestamps and fix tags.
	* 
	* @access protected
	* 
	* @param array &$data An associative array of data to be updated, in
	* the format (field => value).
	* 
	* @return mixed Void on success, Solar_Error object on failure.
	* 
	*/
	
	protected function preUpdate(&$data)
	{
		if (isset($data['tags'])) {
			$data['tags'] = $this->tags->fixString($data['tags']);
		}
		
		$data['ts_mod'] = $this->timestamp();
	}
	
	
	/**
	* 
	* Custom post-update processing to refresh the tags table.
	* 
	* @access protected
	* 
	* @param array &$data An associative array of data to be updated, in
	* the format (field => value).
	* 
	* @return mixed Void on success, Solar_Error object on failure.
	* 
	*/
	
	protected function postUpdate(&$data)
	{
		if (isset($data['tags'])) {
			return $this->tags->refresh(
				'sc_bookmarks',
				$data['id'],
				$data['tags']
			);
		}
	}
}
?>