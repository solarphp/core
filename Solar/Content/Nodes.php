<?php

/**
* 
* Nodes within an area, equivalent to containers for related content parts.
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
* Nodes within an area, equivalent to containers for related content parts.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Content
* 
*/

class Solar_Content_Nodes extends Solar_Base {

	public $table;
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->table = Solar::object('Solar_Content_Nodes_Table');
	}
}
?>