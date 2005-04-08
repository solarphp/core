<?php

/**
* 
* Component module to store tags on related items.
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
* @version $Id: Bookmarks.php 136 2005-04-06 18:15:56Z pmjones $
* 
*/

/**
* Have the Entity class available for extension.
*/
Solar::autoload('Solar_Sql_Entity');


/**
* 
* Component module to store tags on related items.
* 
* @category Solar
* 
* @package Solar_Cell
* 
* @subpackage Solar_Cell_Tags
* 
*/

class Solar_Cell_Tags_Bundle extends Solar_Sql_Entity {
	
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
		
		$schema['tbl'] = 'sc_tags_bundle';
		
		
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
		$schema['col']['tags'] = array(
			'type'    => 'varchar',
			'size'    => 255,
			'require' => true,
			'validate' => array(
				array(
					'regex',
					$this->locale('VALID_TAGS'),
					'/^[A-Za-z0-9_+]*$/'
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
			'tags'   => 'normal',
		);
		
		
		// -------------------------------------------------------------
		// 
		// done!
		// 
		
		return $schema;
	}
	
	
	protected function preInsert(&$data)
	{
		// delimit the tags in the bundle with plus-signs (not spaces,
		// becuase MySQL trims trailing spaces)
		$data['tags'] = '+' . str_replace(' ', '+', $data['tags']) . '+';
	}
	
	public function remove($rel, $rel_id)
	{
		$where = 'rel = ' . $this->quote($rel) . 
			' AND rel_id = ' . $this->quote($rel_id);
		
		return $this->delete($where);
	}
}
?>