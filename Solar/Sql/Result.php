<?php

/**
* 
* Class for iterating through selected row results.
* 
* @category Solar
* 
* @package Solar
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
* Class for iterating through selected row results.
* 
* @category Solar
* 
* @package Solar
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
	* class => (string) The SQL driver class name.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'rsrc'  => null,
		'class' => null
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
		// make a static call to the free() method from the driver
		call_user_func(
			array($this->config['class'], 'free'),
			$this->config['rsrc']
		);
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
		// make a static call to the fetch() method from the driver
		$row = call_user_func(
			array($this->config['class'], 'fetch'),
			$this->config['rsrc']
		);
		
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
		// make a static call to the fetchNum() method from the driver
		return call_user_func(
			array($this->config['class'], 'fetchNum'),
			$this->config['rsrc']
		);
	}
}
?>
