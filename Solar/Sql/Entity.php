<?php

/**
* 
* Class for representing an entity (i.e., a table).
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Sql
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* Needed for data validation.
*/
Solar::autoload('Solar_Valid');

/**
* 
* Class for representing an entity (i.e., a table).
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Sql
* 
*/

abstract class Solar_Sql_Entity extends Solar_Base {
	
	/**
	* 
	* User-provided configuration.
	* 
	* Keys are:
	* 
	* sql => (string|array) Name of the shared SQL object, or array of (driver,
	* options) to create a standalone SQL object.
	* 
	* auto_create => (bool)  Whether or not to auto-create the table.
	* 
	* per_page => (int) The number of rows to return per page.
	* 
	* Note that there is no locale key; to keep from messing up extended
	* classes, we only return the error key as the localized string.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	
	public $config = array(
		'sql'           => 'sql',
		'auto_create'   => true,
		'rows_per_page' => 10,
	);
	
	
	/**
	* 
	* A Solar_Sql object.
	* 
	* @access protected
	* 
	* @var object
	* 
	*/
	
	protected $sql;
	
	
	/**
	*
	* The entity schematic as an array.
	* 
	* The name of the entity table:
	* 
	* $this->schema['tbl'] = 'table_name';
	*
	* The field specification array for all columns in this entity table:
	* 
	* <code>
	* $this->schema['col'] = array(
	*   'fieldOne' => array(
	*     'type'    => bool|char|int|etc,
	*     'size'    => total length for char|varchar|numeric
	*     'scope'   => decimal places for numeric
	*     'require' => true|false,
	*     'default' => default value, // if array, a PHP function callback
	*     'primary' => true/false, // if true, cannot update field
	*     'sequence' => '',// use this sequence name to get a value on insert
	*     'validate'   => array(
	*       array('nonBlank', message),
	*       array('custom', message, callback, arg, arg, arg...),
	*       array(...),
	*     ),
	*   ),
	*   'fieldTwo' => array(...)
	* );
	* </code>
	* 
	* The index specification array for all indexes on this table:
	* 
	* <code>
	* $this->schema['idx'] = array(
	*   'name1' => array('normal', 'field'), // single-col
	*   'name2' => array('unique', array('field', 'field', 'field')), // multi-col
	*   'field' => 'unique' // shorthand single-col
	* );
	* </code>
	* 
	* 
	* The list of related tables that this table can join to as an
	* equvalient JOIN (e.g., "JOIN that_table ON us.col_here = them.col_there").
	* 
	* <code>
	* $this->schema['rel'] = array(
	*   'relName1' => array('col_here', 'that_table', 'col_there'),
	*   'relName2' => array(...)
	* );
	* </code>
	* 
	* 
	* An array of predefined SELECT query elements.
	* 
	* <code>
	* $this->schema['qry'] = array(
	*   'name1' => array(
	*     'select' => array('field', 'as' => 'field', ...),
	*     'from'   => array('table', 'as' => 'table', ...),
	*     'join'   => array('relName1', ...)
	*     'where'  => string,
	*     'group'  => string,
	*     'having' => string,
	*     'order'  => string,
	*     'fetch'  => 'All'|'One'|'Row'|'Col'|'Pair'|'Assoc',
	*     'count'  => the field name to use for selectCount
	*   ),
	*   'name2' => array(...)
	* );
	* </code>
	* 
	* 
	* An array of predefined form-building hints.
	* 
	* More accurately, these can be called block-building hints for forms.
	* The form() command will only generate elements, not an entire form.
	* 
	* <code>
	* $this->schema['frm'] = array(
	*   'name1' => array(
	*       'field1' => array(
	*         'type'  => HTML form element type (or false to not-include)
	*         'label' => label,
	*         'opts'  => array(value => label)
	*         'attr'  => array(attrib => value)
	*       ),
	*       'field2'  => array(...),
	*    ),
	*    'name2' => array(...)
	* );
	* </code>
	* 
	* @access protected
	*
	* @var array
	*
	*/

	protected $schema = array(
		'tbl' => null,
		'col' => null,
		'idx' => null,
		'rel' => null,
		'qry' => null,
		'frm' => null
	);

	
	/**
	* 
	* Constructor.
	* 
	*/
	
	public function __construct($config = null)
	{
		// default values for reserved $config keys
		$default = array(
			'sql'           => 'sql',
			'auto_create'   => true,
			'rows_per_page' => 10
		);
		
		// make sure we have default values for the reserved $config keys
		foreach ($default as $key => $val) {
			if (! isset($this->config[$key])) {
				$this->config[$key] = $val;
			}
		}
		
		// now override with any values from the constructor
		parent::__construct($config);
		
		// set up the schema property
		$this->schema = array_merge($this->schema, $this->getSchema());
		
		// connect to the database
		if (is_string($this->config['sql'])) {
			// use a shared object
			$this->sql = Solar::shared($this->config['sql']);
		} else {
			// use a standalone object
			$this->sql = Solar::object(
				$this->config['sql'][0],
				$this->config['sql'][1]
			);
		}
		
		// attempt to create the table
		if ($this->config['auto_create']) {
			$this->createTable();
		}
	}

	
	/**
	* 
	* Build and return the entity schematic array.
	* 
	* Override this in your extended class to build the schematic.  Remember,
	* this will get called every time the class in instantiated, so try not
	* to do expensive stuff (e.g., parsing an XML file).
	* 
	* @access protected
	* 
	* @return array The schema array.
	* 
	*/
	
	protected function getSchema()
	{
		return array(
			'tbl' => null,
			'col' => null,
			'idx' => null,
			'rel' => null,
			'qry' => null,
			'frm' => null
		);
	}
	
	
	/**
	* 
	* Quotes a value, making it safe for SQL statements.
	* 
	* @access public
	* 
	* @param mixed $value The value to quote.
	* 
	* @return mixed The quoted value.
	* 
	*/
	
	public function quote($value)
	{
		return $this->sql->quote($value);
	}
	
	
	/**
	* 
	* Calculates the limit count and offset for a given page number.
	* 
	* @access protected
	* 
	* @param int $page The page number to get limits for.
	* 
	* @return array An associative array with 'count' and 'offset' values.
	* 
	*/
	
	protected function pageLimit($page = null)
	{
		if ($page !== null && $page >= 0) {
			$count  = $this->config['rows_per_page'];
			$offset = $page * $count;
		} else {
			$count = null;
			$offset = null;
		}
		
		return array(
			'count'  => $count,
			'offset' => $offset
		);
	}
	
	/**
	* 
	* Executes a predefined SELECT and returns a result object.
	* 
	* @access public
	* 
	* @param string $name The name of the $select to use.
	* 
	* @param string $filter Additional query terms to add to the
	* predefined WHERE portion of the $select.
	* 
	* @param string $order Overrides the ORDER portion of the predefined
	* $select.
	* 
	* @param int $page The page number to get.
	* 
	* @return object A Solar_Sql_Result object.
	* 
	*/
	
	public function selectResult($name, $filter = null, $order = null,
		$page = null)
	{
		// get the base statement and check it
		$stmt = $this->buildSelect($key, $filter, $order);
		if (Solar::isError($stmt)) {
			return $stmt;
		}
		
		// execute the statement and return the result object
		$limit = $this->pageLimit($page);
		return $this->sql->exec($stmt, $limit['count'], $limit['offset']);
	}
	
	
	/**
	* 
	* Executes a predefined SELECT and returns the fetched rows.
	* 
	* @access public
	* 
	* @param string $name The name of the $select to use.
	* 
	* @param string $filter Additional query terms to add to the
	* predefined WHERE portion of the $select.
	* 
	* @param string $order Overrides the ORDER portion of the predefined
	* $select.
	* 
	* @param int $page The page number to get.
	* 
	* @return array|string Usually an array of rows, but in the case of
	* fetchOne it will be a string.
	* 
	*/
	
	public function selectFetch($name, $filter = null, $order = null,
		$page = null)
	{
		// get the base statement and check it
		$stmt = $this->buildSelect($name, $filter, $order);
		if (Solar::isError($stmt)) {
			return $stmt;
		}
		
		// what kind fetch are we doing here? default is 'All'.
		$fetch = isset($this->schema['qry'][$name]['fetch'])
			? ucwords(strtolower($this->schema['qry'][$name]['fetch']))
			: 'All';
		$fetch = 'fetch' . $fetch;
		
		// return the fetched results.
		$limit = $this->pageLimit($page);
		return $this->sql->$fetch($stmt, $limit['count'], $limit['offset']);
	}
	
	
	/**
	* 
	* Executes a predefined SELECT and returns the count of rows.
	* 
	* @access public
	* 
	* @param string $name The name of the $select to use.
	* 
	* @param string $filter Additional query terms to add to the
	* predefined WHERE portion of the $select.
	* 
	* @return string A numeric row count.
	* 
	*/
	
	public function selectCount($name, $filter = null)
	{
		// does the select key exist?
		$tmp = array_keys($this->schema['qry']);
		if (! in_array($name, $tmp)) {
			return $this->error(
				'ERR_QUERY_NOT_FOUND',
				array('name' => $name),
				E_USER_WARNING
			);
		}
        
        // create a select key name for this count-query
        $count_key = '__count__' . $name;
        
        // has a count-query for the SQL key already been created?
        if (! isset($this->schema['qry'][$count_key])) {
            
            // we've not asked for a count on this query yet.
            // get the elements of the query ...
            $count_sql = $this->schema['qry'][$name];
            
            // is a count-field set for the query?
            if (! isset($count_sql['count']) ||
            	trim($count_sql['count']) == '') {
                $count_sql['count'] = '*';
            }
            
            // replace the fields with a COUNT() command
            $count_sql['fields'] = array(
            	'row_count' => "COUNT({$count_sql['count']})"
            );
            
            // replace the 'fetch' key so we only get the one field
            $count_sql['fetch'] = 'one';
            
            // create the new count-query in the $sql array
            $this->schema['qry'][$count_key] = $count_sql;
        }
        
        // retrieve the count results
        return $this->selectFetch($count_key, $filter);
	}
	
	
	/**
	* 
	* Executes a predefined SELECT and returns the number of pages.
	* 
	* @access public
	* 
	* @param string $name The name of the $select to use.
	* 
	* @param string $filter Additional query terms to add to the
	* predefined WHERE portion of the $select.
	* 
	* @return int A numeric count of pages.
	* 
	*/
	
	public function selectPages($name, $filter = null)
	{
		$result = $this->selectCount($name, $filter);
		if (! Solar::isError($result) && $result > 0) {
			$result = ceil($result / $this->config['rows_per_page']);
		}
		return $result;
	}
	
	
	/**
	* 
	* Validates and inserts data into the table.
	* 
	* @access public
	* 
	* @param array &$data An associative array of data to be inserted, in
	* the format (field => value).
	* 
	* @return mixed Boolean true on success, Solar_Error object on failure.
	* 
	*/
	
	public function insert($data)
	{
		// set defaults
		$data = array_merge($this->defaultRow(), $data);
		
		// forcibly add sequential values
		foreach ($this->schema['col'] as $field => $info) {
			// does this field use a sequence?
			if (! empty($info['sequence'])) {
				// yes, override any given values
				$data[$field] = $this->sql->nextSequence($info['sequence']);
			}
		}
		
		// apply custom insert pre-processing.
		$result = $this->preInsert($data);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// validate and recast the data.
		$result = $this->validate($data);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// attempt the insert.
		$result = $this->sql->insert($this->schema['tbl'], $data);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// do any post-insert tasks
		return $this->postInsert($data);
	}
	
	
	/**
	* 
	* Custom pre-insert processing.
	* 
	*/
	
	protected function preInsert(&$data)
	{
	}
	
	
	/**
	* 
	* Custom post-insert processing.
	* 
	*/
	
	protected function postInsert(&$data)
	{
		// loop through the fields and try to find a primary key,
		// and return that value if we find one.
		foreach ($this->schema['col'] as $field => $info) {
			if (! empty($info['primary']) && $info['primary']) {
				return $data[$field];
			}
		}
	}
	
	
	/**
	* 
	* Validates and updates data in the table based on a WHERE clause.
	* 
	* @access public
	* 
	* @param array $data An associative array of data to be updated, in
	* the format (field => value).
	* 
	* @param string $where An SQL WHERE clause limiting the updated rows.
	* 
	* @return mixed Boolean true on success, Solar_Error object on failure.
	* 
	*/
	
	public function update($data, $where)
	{
		// disallow the changing of primary key data
		foreach (array_keys($data) as $field) {
			// get the 'primary' flag
			$primary = isset($this->schema['col'][$field]['primary'])
				? $this->schema['col'][$field]['primary']
				: false;
			// unset if primary
			if ($primary) {
				unset($data[$field]);
			}
		}
		
		// custom pre-update tasks
		$result = $this->preUpdate($data);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// validate-and-recast the data
		$result = $this->validate($data);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// attempt the update
		$result = $this->sql->update($this->schema['tbl'], $data, $where);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// post-update tasks
		return $this->postUpdate($data);
	}
	
	
	/**
	* 
	* Custom pre-update processing.
	* 
	*/
	
	protected function preUpdate(&$data)
	{
	}
	
	
	/**
	* 
	* Custom pre-update processing.
	* 
	*/
	
	protected function postUpdate(&$data)
	{
	}
	
	
	/**
	* 
	* Deletes rows from the table based on a WHERE clause.
	* 
	* @todo Needs to honor joined tables.
	* 
	* @access public
	* 
	* @param string $where An SQL WHERE clause limiting the deleted rows.
	* 
	* @return mixed Boolean true on success, Solar_Error object on failure.
	* 
	*/
	
	public function delete($where)
	{
		return $this->sql->delete($this->schema['tbl'], $where);
	}
	
	
	/**
	* 
	* Returns a data array with field keys and default values.
	* 
	* @access public
	* 
	* @return array
	* 
	*/
	
	public function defaultRow($blank = false)
	{
		$data = array();
		foreach ($this->schema['col'] as $field => $info) {
			
			// is there a default specified?
			if (! $blank && array_key_exists('default', $info)) {
				
				// yes.  is it a callback or a literal?
				if (is_array($info['default'])) {
					// callback
					$func = array_shift($info['default']);
					$data[$field] = call_user_func_array(
						$func,
						$info['default']
					);
				} else {
					// literal
					$data[$field] = $info['default'];
				}
				
			} else {
				// asked for blank, or no default given
				$data[$field] = null;
			}
		}
		return $data;
	}
	
	
	/**
	* 
	* Override this to generate default field values.
	* 
	* @access protected
	* 
	* @param string $field The field name to generate a default value for.
	* 
	* @return mixed A default value for the field.
	* 
	*/
	
	public function defaultCol($field)
	{
		return null;
	}
	
	
	/**
	* 
	* Returns an array of element "hints" for how to build a form.
	* 
	* Primarily for use with Solar_Form.
	* 
	* @access public
	* 
	* @param array $data An associative array of fields and
	* default values in the format (field => value).
	* 
	* @param array $msg An associative array of fields and
	* validation messages in the following format:
	* (field => array(message, message, ...)).
	* 
	* @return array An array of hints keyed on field names.
	* 
	*/
	
	public function formElements($name, $data = null, $msg = null)
	{
		// the basic form hints
		if (empty($this->schema['frm'][$name])) {
			return $this->error(
				'ERR_FORM_NOT_FOUND',
				array('name' => $name),
				E_USER_WARNING
			);
		} else {
			$elements = $this->schema['frm'][$name];
		}
		
		// make sure we have data for all fields, even if blank.
		settype($data, 'array');
		$data = array_merge($this->defaultRow(true), $data);
		
		// loop through each of the hints
		foreach ($elements as $field => $info) {
			
			// make sure there are keys for all info.
			$val = (isset($data[$field])) ? $data[$field] : null;
			$tmp = array(
				'name'    => $field,
				'type'    => null,
				'label'   => null,
				'value'   => $val,
				'require' => null,
				'disable' => false,
				'options' => array(),
				'attribs' => array(),
				'feedback' => array(),
				'validate' => array(),
			);
			$info = array_merge($tmp, $info);
			
			// if the field name does not exist, skip all the extras;
			// the form element must be for something other than the
			// table entity.
			if (! isset($this->schema['col'][$field])) {
				$elements[$field] = $info;
				continue;
			}
			
			// if no element type is specified...
			if (is_null($info['type'])) {
				
				if (! empty($this->schema['col'][$field]['primary']) &&
					$this->schema['col'][$field]['primary'] == true) {
					
					// hide primary keys but keep them in the form
					$info['type'] = 'hidden';
					
				} elseif (! empty($this->schema['col'][$field]['sequence'])) {
					
					// don't allow entry of sequential values
					$info['type'] = 'static';
				
				} else {
				
					// base the element type on the field type.
					switch ($this->schema['col'][$field]['type']) {
					
					case 'bool':
						$info['type'] = 'checkbox';
						$info['options'] = array(0,1);
						break;
						
					case 'clob':
						$info['type'] = 'textarea';
						break;
						
					case 'date':
					case 'time':
					case 'timestamp':
						$info['type'] = $this->schema['col'][$field]['type'];
						break;
						
					default:
						// if there are opts, make it a select.
						// in all other cases, make it text.
						if (count($info['options']) > 0) {
							$info['type'] = 'select';
						} else {
							$info['type'] = 'text';
						}
						break;
					}
				}
			}
			
			// if there's no label, use the field name (modified).
			if (is_null($info['label'])) {
				$tmp = str_replace('_', ' ', $field);
				$tmp = ucwords(strtolower($tmp));
				$info['label'] = $tmp;
			}
			
			// if "required" not specified, check field spec and validations
			if (is_null($info['require'])) {
				
				// check the field spec
				if (! empty($this->schema['col'][$field]['require'])) {
					// set to the field spec 'require' value
					$info['require'] = $this->schema['col'][$field]['require'];
				}
				
				if (! empty($this->schema['col'][$field]['validate'])) {
					// look for a 'require' validation
					foreach ($this->schema['col'][$field]['validate'] as $val) {
						if ($val[0] == 'require') {
							$info['require'] == true;
							break;
						}
					}
				}
				
				// if still null, set to false
				if (is_null($info['require'])) {
					$info['require'] = false;
				}
			}
			
			// if there's a field size component, set the
			// maximum length of text elements if one
			// has not already been specified.
			/** @todo Add +1 or +2 to 'size' for 'numeric' types? */
			if ($info['type'] == 'text' &&
				isset($this->schema['col'][$field]['size']) &&
				! isset($info['attribs']['maxlength'])) {
				
				// set the maxlength attribute
				$info['attribs']['maxlength'] = $this->schema['col'][$field]['size'];
			}
			
			// if the field is a checkbox, and there are no options listed,
			// set them to '1' and '0'
			if ($info['type'] == 'checkbox' && count($info['options']) == 0) {
				$info['options'] = array('1', '0');
			}
			
			// if the field is a select or radio, and there are no
			// options listed, look at the validation 'inlist' field
			// (if there is one) and use those values.
			if (($info['type'] == 'radio' || $info['type'] == 'select') &&
				count($info['options']) == 0) {
				
				// look for an  'inlist' validation
				foreach ($this->schema['col'][$field]['validate'] as $val) {
					if ($val[0] == 'inList') {
						// found one, add it as the 'opts' array
						foreach ($val[2] as $v) {
							$info['options'][$v] = $v;
						}
						// done, leave the loop
						break;
					}
				}
			}
			
			// add validation callbacks, if any
			
			// add feedback messages, if any
			if (isset($msg[$field])) {
				// always add as an array
				settype($msg[$field], 'array');
				$info['feedback'] = $msg[$field];
			}
			
			// done with this element, store it back into the hints
			$elements[$field] = $info;
		}
		
		// done building form hints
		return $elements;
	}
	
	
	/**
	* 
	* Creates the entity table if it does not exist.
	* 
	* If creation fails, whether for the table itself or for indexes on
	* that table, this method will drop the newly-created table.
	* 
	* @access protected
	* 
	* @return mixed Boolean true on if the table was created, boolean
	* false if the table already exists, or Solar_Error object if creation
	* failed.
	* 
	*/
	
	protected function createTable()
	{
		// is a table with the same name already there?
		$tmp = $this->sql->listTables();
		$here = strtolower($this->schema['tbl']);
		foreach ($tmp as $there) {
			if ($here == strtolower($there)) {
				// table already exists
				return false;
			}
		}
		
		// create the table itself
		$result = $this->sql->createTable(
			$this->schema['tbl'],
			$this->schema['col']
		);
		
		// was there a problem creating the table?
		if (Solar::isError($result)) {
		
			// add another error on top of it
			$result->push(
				get_class($this),
				'ERR_TABLE_NOT_CREATED',
				'ERR_TABLE_NOT_CREATED',
				array('table' => $this->schema['tbl']),
				E_USER_ERROR
			);
			
			// done
			return $result;
		}
		
		// create each of the indexes
		foreach ($this->schema['idx'] as $name => $info) {
		
			// create this index
			$result = $this->sql->createIndex($this->schema['tbl'], $name, $info);
			
			// was there a problem creating the index?
			if (Solar::isError($result)) {
			
				// cancel the whole deal.
				$this->sql->dropTable($this->schema['tbl']);
				
				// add another error on top of it.
				$result->push(
					get_class($this),
					'ERR_TABLE_NOT_CREATED',
					'ERR_TABLE_NOT_CREATED',
					array('table' => $this->schema['tbl']),
					E_USER_ERROR
				);
				
				// done
				return $result;
			}
		}
		
		// creation of the table and its indexed is complete
		return true;
	}
	
	
	/**
	* 
	* Builds an SQL SELECT statement from a $this->schema['qry'] key.
	* 
	* @access protected
	* 
	* @param string $name The name of the $select to use.
	* 
	* @param string $filter Additional query terms to add to the
	* predefined WHERE portion of the $select.
	* 
	* @param string $order Overrides the ORDER portion of the predefined
	* $select.
	* 
	* @return string The SQL SELECT statement as built from the
	* predefined $select.
	* 
	*/
	
	protected function buildSelect($name, $filter = null, $order = null)
	{
		// does the select key exist?
		$tmp = array_keys($this->schema['qry']);
		if (! in_array($name, $tmp)) {
			return $this->error(
				'ERR_QUERY_NOT_FOUND',
				array('name' => $name),
				E_USER_WARNING
			);
		}
		
		// the SQL clause parts and their default values
		$part = array(
			'select' => '*',
			'from'   => $this->schema['tbl'],
			'join'   => null,
			'where'  => null,
			'group'  => null,
			'having' => null,
			'order'  => null
		);
		
		// merge in the defined portions
		$part = array_merge($part, $this->schema['qry'][$name]);
		
		// add the filter to the WHERE part
		if (is_string($filter)) {
			// if a string, we just tack it onto the end
			if (! $part['where']) {
				// no where there, use as the entire part
				$part['where'] .= $filter;
			} else {
				// there's already a where, attach with 'AND'
				$part['where'] .= " AND ($filter)";
			}
		} elseif (is_array($filter)) {
			// the filter is an array, treat it as a set of 
			// data to bind into the 'where' part.  this
			// lets you build fillable where clauses in the
			// predefined queries.
			$part['where'] = $this->sql->bind(
				$part['where'],
				$filter
			);
		}
		
		// override the ORDER part
		if ($order) {
			$part['order'] = $order;
		}
		
		// build up the command string form the parts
		$stmt = '';
		
		// get the SELECT field list
		$tmp = array();
		foreach ( (array) $part['select'] as $key => $val) {
			if (is_int($key)) {
				// the key is numeric, so the value is the field
				$tmp[] = $val;
			} else {
				$tmp[] = "$val AS $key";
			}
		}
		$stmt .= 'SELECT ' . implode(', ', $tmp);
		
		// get the FROM list
		$tmp = array();
		foreach ( (array) $part['from'] as $key => $val) {
			if (is_int($key)) {
				// the key is numeric, so the value is the table
				$tmp[] = $val;
			} else {
				// the key is not numeric, it's the 'as' name
				$tmp[] = "$val AS $key";
			}
		}
		$stmt .= "\nFROM " . implode(', ', $tmp);
		
		// add the JOIN tables, if any
		if (! empty($part['join'])) {
		    if (is_array($part['join'])) {
				foreach ($part['join'] as $key => $val) {
				    $rel = $this->schema['rel'];
					$stmt .= "\nJOIN $rel[2] ON {$this->schema['tbl']}.$rel[1] = $rel[2].$rel[3]";
				}
		    } else {
		        $stmt .= "\n{$part['join']}";
		    }
		}
		
		// add the WHERE clause
		if (! empty($part['where'])) {
			$stmt .= "\nWHERE " . $part['where'];
		}
		
		// add the GROUP clause
		if (! empty($part['group'])) {
			$stmt .= "\nGROUP BY " . $part['group'];
		}
		
		// add the HAVING clause
		if (! empty($part['having'])) {
			$stmt .= "\nHAVING " . $part['having'];
		}
		
		// add the ORDER clause
		if (! empty($part['order'])) {
			$stmt .= "\nORDER BY " . $part['order'];
		}
		
		// done!
		return $stmt;
	}
	
	
	/**
	* 
	* Validates and recasts an array of input/update data in-place.
	* 
	* @access public
	* 
	* @param array &$data An associative array of data as (field => value).
	* Note that this is a reference; the array will be modified in-place.
	* 
	* @return mixed Void if the data is valid, or a Solar_Error object where
	* the 'info' is an array of error messages (field => array(errors)).
	* 
	*/
	
	public function validate(&$data)
	{
		// low and high range values for integers
		$int_range = array(
			'smallint' => array(pow(-2, 15), pow(+2, 15) - 1),
			'int'      => array(pow(-2, 31), pow(+2, 31) - 1),
			'bigint'   => array(pow(-2, 63), pow(+2, 63) - 1)
		);
		
		// all errors captured for all fields
		$all_errors = array();
		
		// the list of available fields; discard data that
		// does not correspond to one of the known fields.
		$known_fields = array_keys($this->schema['col']);
		
		// loop through each data field
		foreach ($data as $field => $value) {
			
			// array of errors in validating this field
			$err = array();
			
			// is this field recognized?
			if (! in_array($field, $known_fields)) {
				// drop it and loop to the next field.
				unset($data[$field]);
				continue;
			}
			
			// if 'require' not present, it's not r
			if (isset($this->schema['col'][$field]['require'])) {
				$require = $this->schema['col'][$field]['require'];
			} else {
				$require = false;
			}
			
			// if null and required, it's not valid.
			if ($require && is_null($value)) {
				$err[] = 'required';
				continue;
			}
			
			// if null and not required, it's valid.
			if (! $require && is_null($value)) {
				continue;
			}
			
			// get the field type
			$type = $this->schema['col'][$field]['type'];
			
			
			// -------------------------------------------------------------
			// 
			// Field-type validation
			// 
			
			switch ($type) {
			
			case 'bool':
				$value = ($value) ? 1 : 0;
				break;
			
			case 'char':
			case 'varchar':
				settype($value, 'string');
				$len = strlen($value);
				$max = $this->schema['col'][$field]['size'];
				if ($len > $max) {
					$err[] = "max size is $max";
				}
				break;
			
			case 'int':
			case 'bigint':
			case 'smallint':
				settype($value, 'int');
				if ($value < $int_range[$type][0] ||
					$value > $int_range[$type][1]) {
					$err[] = 'integer value out of range';
				}
				break;
			
			case 'float':
				settype($value, 'float');
				break;
			
			case 'numeric':
				settype($value, 'float');
				$size = $this->schema['col'][$field]['size'];
				$scope = $this->schema['col'][$field]['scope'];
				if (! Solar_Valid::inScope($value, $size, $scope)) {
					$err[] = 'numeric value out of range';
				}
				break;
			
			case 'date':
				settype($value, 'string');
				if (! Solar_Valid::isoDate($value)) {
					$err[] = 'date not valid';
				}
				break;
			
			case 'time':
				settype($value, 'string');
				if (strlen($value) == 5) {
					// add seconds if only hours and minutes
					$value .= ":00";
				}
				if (! Solar_Valid::isoTime($value)) {
					$err[] = 'time not valid';
				}
				break;
			
			case 'timestamp':
				settype($value, 'string');
				// make sure it's in the format yyyy-mm-ddThh:ii:ss
				$value = substr($value, 0, 10) . 'T' . substr($value, 11, 8);
				if (! Solar_Valid::isoDatetime($value)) {
					$err[] = 'timestamp not valid';
				}
				break;
			}
			
			// -------------------------------------------------------------
			// 
			// Content validation
			// 
			
			// add validation placeholder array if needed
			if (! isset($this->schema['col'][$field]['validate'])) {
				$this->schema['col'][$field]['validate'] = array();
			}
			
			// loop through each validation rule
			foreach ($this->schema['col'][$field]['validate'] as $args) {
				
				// the name of the Solar_Valid method
				$method = array_shift($args);
				
				// the text of the error message
				$text = array_shift($args);
				if (is_null($text)) {
					$text = Solar::locale('Solar', 'ERR_INVALID');
				}
				
				// config is now the remaining arguments,
				// put the value on top of it.
				array_unshift($args, $value);
				
				// call the appropriate Solar_Valid method
				$result = call_user_func_array(
					array('Solar_Valid', $method),
					$args
				);
				
				// was it valid?
				if (! $result) {
					$err[] = $text;
				}
				
			} // endforeach
			
			
			// retain all errors generated for this field
			if (count($err) > 0) {
				$all_errors[$field] = $err;
			}
			
		} // endforeach()
		
		
		// -------------------------------------------------------------
		// 
		// Done.
		// 
		
		if (count($all_errors) > 0) {
			return $this->error(
				'ERR_INVALID',
				$all_errors,
				E_USER_WARNING,
				false // no backtrace
			);
		}
	}
	
	
	/**
	* 
	* Returns the current ISO-standard date (e.g., '1979-11-07').
	* 
	* @access public
	* 
	* @return string The current ISO-standard date (e.g., '1979-11-07').
	* 
	*/
	
	public function date()
	{
		return date('Y-m-d');
	}
	
	
	/**
	* 
	* Returns the current ISO-standard time (e.g., '12:34:56').
	* 
	* @access public
	* 
	* @return string The current ISO-standard date (e.g., '12:34:56').
	* 
	*/
	
	public function time()
	{
		return date('H:i:s');
	}
	
	
	/**
	* 
	* Returns the current ISO-standard timestamp (e.g., '1979-11-07T12:34:56').
	* 
	* @access public
	* 
	* @return string The current ISO-standard timestamp (e.g.,
	* '1979-11-07T12:34:56').
	* 
	*/
	
	function timestamp()
	{
		return substr(date('c'), 0, 19);
	}
}
?>