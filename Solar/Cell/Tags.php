<?php

/**
* 
* Application component module for content tags (a la del.icio.us).
* 
* @category Solar
* 
* @package Solar_Cell
* 
* @subpackage Solar_Cell_Tags
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

/*

How to do inserts and updates?


*/

/**
* 
* Application component module for content tags (a la del.icio.us).
* 
* @category Solar
* 
* @package Solar_Cell
* 
* @subpackage Solar_Cell_Tags
* 
*/

class Solar_Cell_Tags extends Solar_Sql_Entity {
	
	/**
	* 
	* Additional config keys and values.
	* 
	*/
	
	public $config = array(
		'locale'         => 'Solar/Cell/Tags/Locale/',
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
		
		$schema['tbl'] = 'sc_tags';
		
		
		// -------------------------------------------------------------
		// 
		// columns
		// 
		
		// sequential id
		$schema['col']['id'] = array(
			'type'     => 'int',
			'sequence' => 'sc_tags',
			'primary'  => true,
			'require'  => true,
		);
			
		// broad source identifier
		$schema['col']['tbl'] = array(
			'type'    => 'varchar',
			'size'    => 64,
			'require' => true,
		);
		
		// id in the broad source
		$schema['col']['tbl_id'] = array(
			'type'    => 'int',
		);
		
		// the "owner" for this tag (so we can see what user have which tags)
		$schema['col']['user_id'] = array(
			'type'    => 'varchar',
			'size'    => 32,
		);
		
		// the tag list itself
		$schema['col']['tags'] = array(
			'type'     => 'varchar',
			'size'     => 255,
			'validate' => array(
				array(
					'regex',
					$this->locale('VALID_TAGS'),
					'/^[A-Za-z0-9_ ]$/')
			)
		);
		
		// -------------------------------------------------------------
		// 
		// indexes
		// 
		
		$schema['idx'] = array(
			'id'      => 'unique',
			'tbl'     => 'normal',
			'tbl_id'  => 'normal',
			'user_id' => 'normal',
			'tags'    => 'normal',
		);
		
		
		// -------------------------------------------------------------
		// 
		// queries
		// 
		
		// generic list of entries for a resource and id
		$schema['qry']['list'] = array(
			'select' => '*',
			'order'  => '',
			'fetch'  => 'All',
			'count'  => 'id',
		);
		
		// tags for a resource
		$schema['qry']['userItem'] = array(
			'select' => 'tags',
			'where'  => 'user_id = :user_id AND tbl = :tbl AND tbl_id = :tbl_id',
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