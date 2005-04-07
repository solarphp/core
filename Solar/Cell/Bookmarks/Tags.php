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
* @version $Id: Bookmarks.php 136 2005-04-06 18:15:56Z pmjones $
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

class Solar_Cell_Bookmarks_Tags extends Solar_Sql_Entity {
	
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
		
		$schema['tbl'] = 'sc_bookmarks_tags';
		
		
		// -------------------------------------------------------------
		// 
		// columns
		// 
		
		// the tag applies to this bookmark_id
		$schema['col']['bookmark_id'] = array(
			'type'    => 'int',
		);
		
		// a single tag
		$schema['col']['tag'] = array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true,
			'validate' => array(
				array(
					'regex',
					$this->locale('VALID_TAG'),
					'/[A-Za-z0-9_]*/'
				),
			),
		);
		
		
		// -------------------------------------------------------------
		// 
		// indexes
		// 
		
		$schema['idx'] = array(
			'bookmark_id' => 'normal',
			'tag'         => 'normal',
		);
		
		
		// -------------------------------------------------------------
		// 
		// relationships; the format is:
		// 
		// keyword => array(this_col, that_tbl, that_col)
		// 
		// 
		
		$schema['rel'] = array(
			'bookmark_info' => array('bookmark_id', 'sc_bookmarks', 'id'),
		);
		
		// -------------------------------------------------------------
		// 
		// queries
		// 
		
		// list of bookmarks with tags
		$schema['qry']['list'] = array(
			'select' => 'sc_bookmarks.*, sc_bookmarks_tags.tag',
			'join'   => 'bookmark_info',
			'order'  => 'ts_new DESC',
			'fetch'  => 'All'
		);
		
		$schema['qry']['tags'] = array(
			'select' => 'tag',
			'fetch'  => 'Col'
		);
		
		/*
		// one bookmark item; same as list, just with different fetch
		$schema['qry']['item'] = array(
			'select' => '*',
			'where'  => 'id = :id',
			'fetch'  => 'Row'
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
		*/
		
		// -------------------------------------------------------------
		// 
		// done!
		// 
		
		return $schema;
	}
	
	
	/**
	* 
	* Fetch a a generic list of tags.
	* 
	*/
	
	public function fetchList($where = null, $order = null, $page = null)
	{
		return $this->selectFetch('list', $where, $order, $page);
	}
	
	public function forBookmark($bookmark_id, $order = null, $page = null)
	{
		$where = 'bookmark_id = ' . $this->quote($bookmark_id);
		return $this->fetchList($where, $order, $page);
	}
	
	public function refresh($bookmark_id, $tags)
	{
		// make $tags an array
		if (! is_array($tags)) {
			$tags = $this->fixString($tags);
			$tags = explode(' ', $tags);
		}
		
		// error collection object
		$err = Solar::object('Solar_Error');
		
		// get the original set of tags.
		$where = 'bookmark_id = ' . $this->quote($bookmark_id);
		$orig = $this->selectFetch('tags', $where);
		
		// get a diff list so we can insert and delete.
		$diff = $this->diff($orig, $tags);
		
		// are there tags to delete?
		if ($diff['del']) {
		
			// always look up by bookmark_id
			$where = 'bookmark_id = ' . $this->quote($bookmark_id);
			
			// create a list of quoted names
			$tmp = array();
			foreach ($diff['del'] as $tag) {
				$tmp[] = $this->quote($tag);
			}
			
			// and delete the un-needed tags
			$where .= ' AND (tag = ' . implode(' OR tag = ', $tmp) . ')';
			$result = $this->delete($where);
			if (Solar::isError($result)) {
				$err->push($result);
			}
		}
		
		// are there tags to insert?
		if ($diff['ins']) {
			foreach ($diff['ins'] as $tag) {
				// don't insert blanks
				if (trim($tag) == '') {
					continue;
				}
				
				// ok, not a blank, insert it.
				$data = array(
					'bookmark_id' => $bookmark_id,
					'tag' => $tag
				);
				$result = $this->insert($data);
				if (Solar::isError($result)) {
					$err->push($result);
				}
			}
		}
		
		// done!
		if ($err->count() > 0) {
			return $err;
		}
	}
	
	protected function diff($old, $new)
	{
		// find intersections first
		$intersect = array_intersect($old, $new);
		
		// now flip arrays so we can unset easily
		$old = array_flip($old);
		$new = array_flip($new);
		
		// remove intersections from each array
		foreach ($intersect as $val) {
			unset($old[$val]);
			unset($new[$val]);
		}
		
		// keys remaining in $old are to be deleted,
		// keys remaining in $new are to be added
		return array(
			'del' => array_keys($old),
			'ins' => array_keys($new)
		);
	}
	
	
	/**
	* 
	* Fixes tag strings for the database.
	* 
	*/
	
	public function fixString($tags)
	{
		// convert to array from string?
		if (! is_array($tags)) {
			// trim all surrounding spaces and extra spaces
			$tags = trim($tags);
			$tags = preg_replace('/[ ]{2,}/', ' ', $tags);
			
			// convert to array for easy processing
			$tmp = explode(' ', $tags);
		}
		
		// make sure each tag is unique (no double-entries)
		$tmp = array_unique($tmp);
		
		// return as text
		return implode(' ', $tmp);
	}
}
?>