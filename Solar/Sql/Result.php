<?php

/**
* 
* Class for iterating through selected row results.
* 
* @category Solar
* 
* @package Solar_Sql
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id: Result.php,v 1.12 2005/02/08 01:42:26 pmjones Exp $
* 
*/

/**
* 
* Class for iterating through selected row results.
* 
* @category Solar
* 
* @package Solar_Sql
* 
*/

class Solar_Sql_Result extends Solar_Base {
	
	
	/**
	* 
	* User-provided configuration values.
	* 
	* Keys:
	* 
	* rsrc => (resource) Query result resource.
	* 
	* driver => (object) The source SQL driver object.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'rsrc'   => null,
		'driver' => null
	);
	
	
	/**
	* 
	* Frees the result set.
	* 
	* @access public
	* 
	* @return void
	* 
	*/
	
	public function __destruct()
	{
		$this->config['driver']->free($this->config['rsrc']);
	}
	
	
	/**
	* 
	* Fetches an associatve row array and advances to next row.
	* 
	* Always forces the keys to lower-case.
	* 
	* @access public
	* 
	* @return mixed An associative row array, or boolean false when
	* there are no more rows.
	* 
	*/
	
	public function fetch()
	{
		$row = $this->config['driver']->fetch($this->config['rsrc']);
		
		if (is_array($row)) {
			array_change_key_case($row, CASE_LOWER);
		}
		
		return $row;
	}
	
	
	/**
	* 
	* Fetches a numeric row array and advances to next row.
	* 
	* @access public
	* 
	* @return mixed A numeric row array, or boolean false when there
	* are no more rows.
	* 
	*/
	
	public function fetchNum()
	{
		return $this->config['driver']->fetchNum($this->config['rsrc']);
	}
}
?>