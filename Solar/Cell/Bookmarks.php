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
		$schema['col']['ts_new'] = array(
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
			'uri'    => 'normal',
		);
		
		
		// -------------------------------------------------------------
		// 
		// relationships (to the 'tags' table)
		//
		
		// relationship when looking up tags for a user and uri
		$schema['rel']['userTags'] = "sc_tags ON " . 
			// match user_id values
			"sc_bookmarks.user_id = sc_tags.user_id AND " .
			// make sure the tag source is "sc_bookmarks"
			"sc_tags.src = 'sc_bookmarks' AND " .
			// match URI values
			"sc_bookmarks.uri = sc_tags.src_id";
		
		
		// relationship when looking up tags for a uri regardless of user
		$schema['rel']['uriTags'] = "sc_tags ON " . 
			// make sure the tag source is "sc_bookmarks"
			"sc_tags.src = 'sc_bookmarks' AND " .
			// match URI values
			"sc_bookmarks.uri = sc_tags.src_id";
		
		
		// -------------------------------------------------------------
		// 
		// queries
		// 
		
		// list of entries for a user
		$schema['qry']['list'] = array(
			'select' => '*',
			'order'  => '',
			'join'   => $schema['rel']['userTags'],
			'fetch'  => 'All',
			'count'  => 'id',
		);
		
		// bookmarks for a user
		$schema['qry']['item'] = array(
			'select' => '*',
			
			'where'  => 'user_id = :user_id AND src = :src AND src_id = :src_id',
			'fetch'  => 'One'
		);
		
		// all tags for a specific user
		$schema['qry']['userTaglist'] = array(
			'select' => 'DISTINCT tags',
			'where' => 'user_id = :user_id',
			'order' => 'tags',
			'fetch' => 'Col'
		);
		
		
		return $schema;
	}
	
	public function userItem($user_id, $src, $src_id)
	{
		$result = $this->selectFetch(
			'userItem',
			array('user_id' => $user_id, 'src' => $src, 'src_id' => $src_id)
		);
		
		if (! Solar::isError($result)) {
			// trim off extra spaces
			$result = trim($result);
			// return as an array
			$result = explode(' ', $result);
		}
		
		return $result;
	}
	
	public function userTaglist($user_id)
	{
		// get the list of tag sets
		$result = $this->selectFetch(
			'userTaglist', 
			array('user_id' => $user_id)
		);
		
		// was it an error? if so, return.
		if (Solar::isError($result)) {
			return $result;
		}
		
		// create an array of all unique tags
		$list = array();
		foreach ($result as $key => $val) {
			$tmp = explode(' ', $val['tags']);
			$list = array_merge($list, $tmp);
		}
		
		// sort it and return
		asort($list);
		return $list;
	}
	
	protected function preInsert(&$data)
	{
		if (isset($data['tags'])) {
			$data['tags'] = $this->fixTags($data['tags']);
		}
	}
	
	protected function preUpdate(&$data)
	{
		if (isset($data['tags'])) {
			$data['tags'] = $this->fixTags($data['tags']);
		}
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