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
	
	// object for tag searches
	protected $tags;
	
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
		// relationships
		// 
		
		$schema['rel']['search_tags'] = "sc_tags_bundle ON sc_tags_bundle.rel = 'sc_bookmarks'" .
			' AND sc_tags_bundle.rel_id = sc_bookmarks.id';
			
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
		
		// lookup by tag name(s)
		$schema['qry']['tags'] = array(
			'select' => 'sc_bookmarks.*',
			'join'   => 'search_tags',
			'fetch'  => 'All'
		);
		
		// -------------------------------------------------------------
		// 
		// forms
		// 
		
		$schema['frm']['edit'] = array(
			'id' => array('type' => 'hidden'),
			'title' => array(
				'validate' => array(
					array('notBlank', 'Please enter a bookmark title.'),
				)
			),
			'uri' => array(
				'validate' => array(
					array('uri', 'Please enter a valid URI.'),
				),
			),
			'descr' => array(),
			'tags' => array(
				'validate' => array(
					array('regex', 'Please use valid tags.', '/^[A-Za-z0-9_ ]*$/'),
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
		return $this->selectFetch('item', array('id' => $id));
	}
	
	
	/**
	* 
	* Fetch a a generic list of bookmarks.
	* 
	*/
	
	public function fetchList($where = null, $order = null, $page = null)
	{
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
	
	public function forUser($user_id, $order = null, $page = null)
	{
		$where = 'user_id = ' . $this->quote($user_id);
		return $this->fetchList($where, $order, $page);
	}
	
	
	/**
	* 
	* Fetch all the bookmarks with a specific tagset.
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
		// build a base where clause ...
		if ($user_id) {
			// ... to find a given user
			$where = 'user_id = ' . $this->quote($user_id);
		} else {
			// ... for all users
			$where = '1=1';
		}
		
		// convert $tags to array
		if (! is_array($tags)) {
			$tags = $this->tags->fixString($tags);
			$tags = explode(' ', $tags);
		}
		
		// finish the where clause with tags ANDed together
		$tmp = array();
		foreach ($tags as $tag) {
			if (trim($tag) != '') {
				// add to the query
				$tmp[] = 'sc_tags_bundle.tags LIKE ' . $this->quote("%+$tag+%");
			}
		}
		
		if ($tmp) {
			$where .= ' AND ' . implode(' AND ', $tmp);
		}
		
		// done!
		return $this->selectFetch('tags', $where, $order, $page);
	}
	
	
	public function update($data, $id)
	{
		$where = 'id = ' . $this->quote($id);
		return parent::update($data, $where);
	}
	
	/**
	* 
	* Pre-insert data manipulation.
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
	* Post-insert operations; we add to the tags table.
	* 
	*/
	
	protected function postInsert(&$data)
	{
		if (isset($data['tags'])) {
			return $this->tags->refresh('sc_bookmarks', $data['id'], $data['tags']);
		}
	}
	
	
	/**
	* 
	* Pre-update data manipulation.
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
	* Post-update operations; refresh the searchable tags table.
	* 
	*/
	
	protected function postUpdate(&$data)
	{
		if (isset($data['tags'])) {
			return $this->tags->refresh('sc_bookmarks', $data['id'], $data['tags']);
		}
	}
}
?>