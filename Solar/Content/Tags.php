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
}
?>