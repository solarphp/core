<?php

/**
* 
* Component module to search for tags on related items.
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
* @version $Id$
* 
*/

/**
* Have the Entity class available for extension.
*/
Solar::autoload('Solar_Sql_Entity');


/**
* 
* Component module to search for tags on related items.
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
	
	
	public $bundle;
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->bundle = Solar::object('Solar_Cell_Tags_Bundle');
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
		
		$schema['tbl'] = 'sc_tags';
		
		
		// -------------------------------------------------------------
		// 
		// columns
		// 
		
		// the tag applies to an item in this related table
		$schema['col']['rel'] = array(
			'type'     => 'varchar',
			'size'     => 64,
			'require'  => true,
			'validate' => array(
				array(
					'regex',
					$this->locale('VALID_REL'),
					'/^[a-z][a-z0-9_]*$/'
				),
			),
		);
		
		// the tag applies to this ID in the related table
		$schema['col']['rel_id'] = array(
			'type'    => 'int',
		);
		
		// a single tag for the related item
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
			'rel'    => 'normal',
			'rel_id' => 'normal',
			'tag'    => 'normal',
		);
		
		
		// -------------------------------------------------------------
		// 
		// queries
		// 
		
		$schema['qry']['refresh'] = array(
			'select' => 'tag',
			'where'  => 'rel = :rel AND rel_id = :rel_id',
			'fetch'  => 'Col'
		);
		
		
		// -------------------------------------------------------------
		// 
		// done!
		// 
		
		return $schema;
	}
	
	
	/**
	* 
	* Refresh the tags stored for a related item.
	* 
	*/
	
	public function refresh($rel, $rel_id, $tags)
	{
		// error collection object
		$err = Solar::object('Solar_Error');
		
		// fix up the tags param
		$tags = $this->fixString($tags);
		
		// the new set of tags as an array
		$new = explode(' ', $tags);
		
		// the old set of tags
		$tmp = array(
			'rel' => $rel,
			'rel_id' => $rel_id
		);
		$old = $this->selectFetch('refresh', $tmp);
		
		// get a diff list so we can insert and delete.
		$diff = $this->diff($old, $new);
		
		// are there tags to delete?
		if ($diff['del']) {
		
			// always filter by related table and related id
			$where = 'rel = ' . $this->quote($rel) . 
				' AND rel_id = ' . $this->quote($rel_id);
			
			// create a list of quoted tag names from the diff deletes
			$tmp = array();
			foreach ($diff['del'] as $tag) {
				$tmp[] = $this->quote($tag);
			}
			
			// delete the un-needed tags
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
					'rel'    => $rel,
					'rel_id' => $rel_id,
					'tag'    => $tag
				);
				
				$result = $this->insert($data);
				
				if (Solar::isError($result)) {
					$err->push($result);
				}
			}
		}
		
		// now refresh the bundle (for searches) ... but only
		// if the tags changed.
		if ($diff['del'] || $diff['ins']) {
			
			// delete previous
			$this->bundle->remove($rel, $rel_id);
			
			// build data
			$data = array(
				'rel'    => $rel,
				'rel_id' => $rel_id,
				'tags'   => $tags
			);
			
			$result = $this->bundle->insert($data);
			
			if (Solar::isError($result)) {
				$err->push($result);
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
	* Fixes tag strings for the queries.
	* 
	*/
	
	public function fixString($tags)
	{
		// convert to array from string?
		if (! is_array($tags)) {
			
			// convert all "+" to spaces
			$tags = str_replace('+', ' ', $tags);
			
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