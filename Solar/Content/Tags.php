<?php

/**
* 
* Tags on nodes.
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
* @version $Id$
* 
*/

/**
* 
* Tags on nodes.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Content
* 
*/

class Solar_Content_Tags extends Solar_Base {
	
	public $table;
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->table = Solar::object('Solar_Content_Tags_Table');
	}
	
	/**
	* 
	* Normalizes tag strings.
	* 
	* Converts "+" to " ", trims extra spaces, and removes duplicates,
	* but otherwise keeps them in order and space-separated.
	* 
	* Also converts arrays to a normalized tag string.
	* 
	* @access public
	* 
	* @param string|array $tags A space-separated string of tags, or a
	* sequential array of tags.
	* 
	* @return string A space-separated string of tags.
	* 
	*/
	
	public function asString($tags)
	{
		// convert to array from string?
		if (! is_array($tags)) {
			
			// convert all "+" to spaces (this is for URL values)
			$tags = str_replace('+', ' ', $tags);
			
			// trim all surrounding spaces and extra spaces
			$tags = trim($tags);
			$tags = preg_replace('/[ ]{2,}/', ' ', $tags);
			
			// convert to array for easy processing
			$tmp = explode(' ', $tags);
		}
		
		// make sure each tag is unique (no double-entries)
		$tmp = array_unique($tmp);
		
		// return as space-separated text
		return implode(' ', $tmp);
	}
	
	
	/**
	* 
	* Normalizes tag arrays.
	* 
	* Also converts strings to a normalized tag array.
	* 
	* @access public
	* 
	* @param string|array $tags A space-separated string of tags, or a
	* sequential array of tags.
	* 
	* @return string A space-separated string of tags.
	* 
	*/
	
	public function asArray($tags)
	{
		// normalize to string...
		$tags = $this->asString($tags);
		// ... and convert to array
		return explode(' ', $tags);
	}
	
	
	/**
	* 
	* "Refreshes" the tags for a part_id by diff.
	* 
	* @access public
	* 
	* @param int $part_id The part_id to work with.
	* 
	* @param string|array $tags A space-separated string of tags, or a
	* sequential array of tags.
	* 
	* @return string A space-separated string of tags.
	* 
	* @todo Collect errors and return as needed.
	* 
	*/
	
	public function refresh($part_id, $tags)
	{
		$part_id = (int) $part_id;
		
		// get the old set of tags
		$old = $this->fetchForNode($part_id);
		
		// normalize the new tags to an array
		$new = $this->asArray($tags);
		
		// diff the tagsets
		$diff = $this->diff($old, $new);
		
		// delete
		if (! empty($diff['del'])) {
			$list   = $this->sql->quoteSep($diff['del']);
			$where  = 'part_id = ' . $this->sql->quote($part_id);
			$where .= " AND name IN ($list)";
			$this->table->delete($where);
		};
		
		// insert
		foreach ($diff['ins'] as $name) {
			$data = array(
				'part_id' => $part_id,
				'name'    => $name
			);
			$this->table->insert($data);
		}
		
		// done!
	}
	
	
	/**
	* 
	* Determines the diff (delete/insert matrix) between two tag sets.
	* 
	* <code>
	* $old = array('a', 'b', 'c');
	* $new = array('c', 'd', 'e');
	* 
	* // perform the diff
	* $diff = $this->diff($old, $new);
	* 
	* // the results are:
	* // $diff['del'] == array('a', 'b');
	* // $diff['ins'] == array('d', 'e');
	* // 'c' doesn't show up becuase it's present in both sets
	* </code>
	* 
	* @access protected
	* 
	* @param array $old The old (previous) set of tags.
	* 
	* @param array $new The new (current) set of tags.
	* 
	* @return array An associative array of two keys: 'del' (where the
	* value is a sequential array of tags removed from the old set)
	* and 'ins' (where the value is a sequential array of tags to be
	* added from the new set).
	* 
	*/
	
	protected function diff($old, $new)
	{
		// find intersections first
		$intersect = array_intersect($old, $new);
		
		// now flip arrays so we can unset easily by key
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
			'del' => (array) array_keys($old),
			'ins' => (array) array_keys($new)
		);
	}
	
	// fetch all tags on a specific part
	// returns as array(id => name)
	protected function fetchForNode($part_id)
	{
		$select = Solar::object('Solar_Sql_Select');
		$select->from($this->table, array('id', 'name'));
		$select->where('part_id = :part_id');
		$select->order('name ASC');
		$select->bind('part_id', $part_id);
		return $select->fetch('pairs');
	}
	
	// fetch all tags used by a specific user
	// returns as array(id => name)
	protected function fetchForUser($handle)
	{
		$select = Solar::object('Solar_Sql_Select');
		$select->distinct();
		$select->from($this->table, array('id', 'name'));
		$select->join('nodes', 'tags.part_id = nodes.id');
		$select->where('nodes.owner_handle = :handle');
		$select->order('name ASC');
		$select->bind('handle', $handle);
		return $select->fetch('pairs');
	}
}
?>