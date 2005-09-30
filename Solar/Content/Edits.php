<?php

/**
* 
* Individual parts of content within a node: wiki page, file atch, comment, etc.
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
* Individual parts of content within a node: wiki page, file atch, comment, etc.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Content
* 
*/

class Solar_Content_Edits extends Solar_Base {
	
	public $table;
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->table = Solar::object('Solar_Content_Edits_Table');
	}
	
}
?>