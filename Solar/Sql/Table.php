<?php

/**
* 
* Class for representing an SQL table.
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
* 
* Class for representing an SQL table.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Sql
* 
*/

class Solar_Sql_Table extends Solar_Base {
	
	
	/**
	* 
	* User-provided configuration.
	* 
	* Keys are:
	* 
	* sql => (string|array) Name of the shared SQL object, or array of (driver,
	* options) to create a standalone SQL object.
	* 
	* locale => (string) Path to locale files.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $config = array(
		'sql'    => 'sql',
	);
	
	
	/**
	* 
	* The table name.
	* 
	* @access protected
	* 
	* @var string
	* 
	*/
	
	protected $name = null;
	
	
	/**
	* 
	* The column specification array for all columns in this table.
	* 
	* Each element in this array looks like this:
	* 
	* <code>
	* $col = array(
	*     'colName' => array(
	*         'name'    => (string) the colName, same as the key
	*         'type'    => (string) char, varchar, date, etc
	*         'size'    => (int) column size
	*         'scope'   => (int) decimal places
	*         'valid'   => (array) Solar_Valid methods and args
	*         'require' => (bool) is this a required (non-null) column?
	*         'seqname' => (string) use this auto-sequence
	*         'default' => (string|array) default value
	*         'primary' => (bool) is this part of the primary key?
	*      ),
	* );
	* </code>
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $col = array();
	
	
	/**
	* 
	* The index specification array for all indexes on this table:
	* 
	* @access protected
	* 
	* @var array
	* 
	* @see addIndex()
	* 
	*/

	protected $idx = array();
	
	
	/**
	* 
	* The SQL object.
	* 
	* @access protected
	* 
	* @var object
	* 
	*/

	protected $sql = null;
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	* @param array $config An array of user-defined configuration
	* values.
	* 
	* @return void
	* 
	*/

	public function __construct($config = null)
	{
		// main construction
		parent::__construct($config);
		
		// perform column and index setup, then fix everything.
		$this->setup();
		$this->fixSetup();
		
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
		
		// auto-create if needed
		$this->autoCreate();
	}
	
	
	/**
	* 
	* Allows reading of protected properties.
	* 
	* @access public
	* 
	* @param string $key The property name ('col', 'idx', or 'name').
	* 
	* @return mixed The property value.
	* 
	*/

	public function __get($key = null)
	{
		$prop = array('col', 'idx', 'name');
		if (in_array($key, $prop)) {
			return $this->$key;
		} else {
			return null;
		}
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
	* @return mixed The inserted data on success, Solar_Error object on failure.
	* 
	*/
	
	public function insert($data)
	{
		// set defaults
		$data = array_merge($this->getDefault(), $data);
		
		// forcibly add sequential values
		foreach ($this->col as $field => $info) {
			// does this field use a sequence?
			if (! empty($info['seqname'])) {
				// yes, override any given values
				$data[$field] = $this->sql->nextSequence($info['seqname']);
			}
		}
		
		// validate and recast the data.
		$result = $this->autoValid($data);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// attempt the insert.
		$result = $this->sql->insert($this->name, $data);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// return the data as inserted
		return $data;
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
	* @param string $where An SQL WHERE clause limiting the updated
	* rows.
	* 
	* @return mixed The updated data on success, Solar_Error object on
	* failure.
	* 
	*/
	
	public function update($data, $where)
	{
		// retain primary key data in this array for return values
		$retain = array();
		
		// disallow the changing of primary key data
		foreach (array_keys($data) as $field) {
		
			// get the 'primary' flag
			$primary = isset($this->col[$field]['primary'])
				? $this->col[$field]['primary']
				: false;
				
			// retain and unset if primary
			if ($primary) {
				$retain[$field] = $data[$field];
				unset($data[$field]);
			}
			
		}
		
		// validate and recast the data
		$result = $this->autoValid($data);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// attempt the update
		$result = $this->sql->update($this->name, $data, $where);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// restore retained primary key data and return
		$data = array_merge($data, $retain);
		return $data;
	}
	
	
	/**
	* 
	* Deletes rows in the table based on a WHERE clause.
	* 
	* @access public
	* 
	* @param string $where An SQL WHERE clause limiting the deleted rows.
	* 
	* @return mixed Void on success, Solar_Error object on failure.
	* 
	*/
	
	public function delete($where)
	{
		$result = $this->sql->delete($this->name, $where);
		return $result;
	}
	
	
	/**
	* 
	* Convenience method to select rows from this table.
	* 
	* @access public
	* 
	* @param string $type The type of fetch to execute: 'all', 'one',
	* 'row', etc. Default is 'result'.
	* 
	* @param array|string $where An SQL WHERE clause to filter results. 
	* May be an array:  If the key is a string, it's assumed to be a
	* column name and the value is the equality comparison; the value is
	* automatically bound into the query.  If the key is an integer, the
	* value is a custom where clause.  Alternatively, $where may be a
	* string, in which case it is a user-defined WHERE clause in its
	* entirety (no binding).
	* 
	* @param array|string $order An SQL ORDER clause, e.g. an array of
	* column names.
	* 
	* @param int $page The page-number of results to retrieve.
	* 
	* @return array
	* 
	*/
	
	public function select($type = 'result', $where = null,
		$order = null, $page = null)
	{
		// selection tool
		$select = Solar::object('Solar_Sql_Select');
		
		// all columns
		$select->cols('*');
		
		// from this table
		$select->from($this->name);
		
		// data to bind into the query
		$data = array();
		
		// where clause?
		if (is_array($where) || is_object($where)) {
			foreach ((array) $where as $key => $val) {
				if (is_int($col)) {
					// custom user-defined clause
					$select->where($val);
				} else {
					// equality key-value pair of "column => value"
					$select->where("$key = :$key");
					// add to the binding data
					$data[$key] = $val;
				}
			}
		} else {
			// ... user-specified clause
			$select->where($where);
		}
		
		// order by?
		$select->order($order);
		
		// paging?
		$select->limitPage($page);
		
		// bind data
		$select->bind($data);
		
		// fetch and return results
		$result = $select->fetch($type);
		return $result;
	}
	
	
	/**
	* 
	* Returns a data array with column keys and default values.
	* 
	* @access public
	* 
	* @param string|array The column(s) to get defaults for; if
	* none specified, gets defaults for all columns.
	* 
	* @return array An array of key-value pairs where the key is
	* the column name and the value is the default column value.
	* 
	*/
	
	public function getDefault($spec = null)
	{
		// the array of default data
		$data = array();
		
		// if no columns specified, use all columns
		if (is_null($spec)) {
			$spec = array_keys($this->col);
		} else {
			settype($spec, 'array');
		}
		
		// loop through each specified column and collect default data
		foreach ($spec as $name) {
			
			// skip columns that don't exist
			if (empty($this->col[$name])) {
				continue;
			}
			
			// get the column info
			$info = $this->col[$name];
			
			// is there a default set?
			if (empty($info['default'])) {
				// no default, so it's null.
				$data[$name] = null;
				continue;
			}
			
			// yes, so get it based on the kind of default.
			switch ($info['default']['type']) {
			
			case 'callback':
				$args = $info['default']['args'];
				$func = array_shift($args);
				$data[$name] = call_user_func_array($func, $args);
				break;
			
			case 'literal':
				$data[$name] = $info['default']['args'];
				break;
			
			default:
				$data[$name] = null;
			}
		}
		
		// done!
		return $data;
	}
	
	
	// -----------------------------------------------------------------
	// 
	// Support and management methods.
	// 
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Use this to set up extended table objects.
	* 
	* @access protected
	* 
	* @return void
	* 
	*/

	protected function setup()
	{
	}
	
	
	/**
	* 
	* Fixes the $col and $idx properties after user setup.
	* 
	* @access protected
	* 
	* @return void
	* 
	*/

	protected final function fixSetup()
	{
		// a baseline column definition
		$basecol = array(
			'name'    => null,
			'type'    => 'varchar',
			'size'    => 255,
			'scope'   => 0,
			'valid'   => array(),
			'require' => false,
			'seqname' => null,
			'default' => null,
			'primary' => false
		);
		
		// fix up each column in the schema
		foreach ($this->col as $name => $info) {
		
			// fill in missing elements
			$info = array_merge($basecol, $info);
			
			// make sure there's a name
			$info['name'] = $name;
			
			// force 'valid' to an array of validations
			settype($info['valid'], 'array');
			
			// if 'default' is not already an array, make it
			// one as a literal.  this lets you avoid the array
			// when setting up simple literals.
			if (! is_array($info['default'])) {
				$info['default'] = array('literal', $info['default']);
			}
			
			// save back into the column info
			$this->col[$name] = $info;
		}
	}
	
	
	/**
	* 
	* Creates the table in the database if it does not already exist.
	* 
	* @access protected
	* 
	* @return mixed Solar_Error if there were creation errors (whether
	* with the table or its indexes), false if the table already existed
	* and didn't need to be created, or true if the table did not exist
	* and was successfully created.
	* 
	*/

	protected final function autoCreate()
	{
		// is a table with the same name already there?
		$tmp = $this->sql->listTables();
		$here = strtolower($this->name);
		foreach ($tmp as $there) {
			if ($here == strtolower($there)) {
				// table already exists
				return false;
			}
		}
		
		// create the table itself
		$result = $this->sql->createTable(
			$this->name,
			$this->col
		);
		
		// was there a problem creating the table?
		if (Solar::isError($result)) {
		
			// add another error on top of it
			$result->push(
				get_class($this),
				'ERR_TABLE_NOT_CREATED',
				$this->locale('ERR_TABLE_NOT_CREATED'),
				array('table' => $this->name),
				E_USER_ERROR
			);
			
			// done
			return $result;
		}
		
		// create each of the indexes
		foreach ($this->idx as $name => $info) {
		
			// create this index
			$result = $this->sql->createIndex($this->name, $name, $info);
			
			// was there a problem creating the index?
			if (Solar::isError($result)) {
			
				// cancel the whole deal.
				$this->sql->dropTable($this->name);
				
				// add another error on top of it.
				$result->push(
					get_class($this),
					'ERR_TABLE_NOT_CREATED',
					$this->locale('ERR_TABLE_NOT_CREATED'),
					array('table' => $this->name),
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
	* Validates and recasts an array of input/update data in-place.
	* 
	* @access protected
	* 
	* @param array &$data An associative array of data as (field => value).
	* Note that this is a reference; the array will be modified in-place.
	* 
	* @return mixed Void if the data is valid, or a Solar_Error object where
	* the 'info' is an array of error messages (field => array(errors)).
	* 
	* @todo Return a Solar_Error stack proper, not an info array.
	* 
	*/
	
	protected final function autoValid(&$data)
	{
		// object methods for validation
		$valid = Solar::object('Solar_Valid');
		
		// low and high range values for integers
		$int_range = array(
			'smallint' => array(pow(-2, 15), pow(+2, 15) - 1),
			'int'      => array(pow(-2, 31), pow(+2, 31) - 1),
			'bigint'   => array(pow(-2, 63), pow(+2, 63) - 1)
		);
		
		// collect all errors captured for all fields
		$err = Solar::object('Solar_Error');
		
		// the list of available fields; discard data that
		// does not correspond to one of the known fields.
		$known = array_keys($this->col);
		
		// loop through each data field
		foreach ($data as $field => $value) {
			
			// is this field recognized?
			if (! in_array($field, $known)) {
				// drop it and loop to the next field.
				unset($data[$field]);
				continue;
			}
			
			// if 'require' not present, it's not required
			if (isset($this->col[$field]['require'])) {
				$require = $this->col[$field]['require'];
			} else {
				$require = false;
			}
			
			// if null and required, it's not valid.
			if ($require && is_null($value)) {
				$err->push(
					get_class($this),
					'ERR_DATA_REQUIRED',
					array('col' => $field),
					E_NOTICE,
					false
				);
				continue;
			}
			
			// if null and not required, it's valid.
			if (! $require && is_null($value)) {
				continue;
			}
			
			// get the field type
			$type = $this->col[$field]['type'];
			
			
			// -------------------------------------------------------------
			// 
			// Type validation
			// 
			
			switch ($type) {
			
			case 'bool':
				$value = ($value) ? 1 : 0;
				break;
			
			case 'char':
			case 'varchar':
				settype($value, 'string');
				$len = strlen($value);
				$max = $this->col[$field]['size'];
				if ($len > $max) {
					$err->push(
						get_class($this),
						'ERR_DATA_MAXSIZE',
						$this->locale('ERR_DATA_MAXSIZE'),
						array(
							'col' => $field,
							'max' => $max,
							'value' => $value
						),
						E_NOTICE,
						false
					);
				}
				break;
			
			case 'int':
			case 'bigint':
			case 'smallint':
				settype($value, 'int');
				if ($value < $int_range[$type][0] ||
					$value > $int_range[$type][1]) {
					$err->push(
						get_class($this),
						'ERR_DATA_INTRANGE',
						$this->locale('ERR_DATA_INTRANGE'),
						array(
							'col' => $field,
							'min' => $int_range[$type][0],
							'max' => $int_range[$type][1],
							'value' => $value
						),
						E_NOTICE,
						false
					);
				}
				break;
			
			case 'float':
				settype($value, 'float');
				break;
			
			case 'numeric':
				settype($value, 'float');
				$size = $this->col[$field]['size'];
				$scope = $this->col[$field]['scope'];
				if (! $valid->inScope($value, $size, $scope)) {
					$err->push(
						get_class($this),
						'ERR_DATA_NUMRANGE',
						$this->locale('ERR_DATA_NUMRANGE'),
						array(
							'col' => $field,
							'size' => $size,
							'scope' => $scope,
							'value' => $value
						),
						E_NOTICE,
						false
					);
				}
				break;
			
			case 'date':
				settype($value, 'string');
				if (! $valid->isoDate($value)) {
					$err->push(
						get_class($this),
						'ERR_DATA_DATE',
						$this->locale('ERR_DATA_DATE'),
						array(
							'col' => $field,
							'value' => $value
						),
						E_NOTICE,
						false
					);
				}
				break;
			
			case 'time':
				settype($value, 'string');
				if (strlen($value) == 5) {
					// add seconds if only hours and minutes
					$value .= ":00";
				}
				if (! $valid->isoTime($value)) {
					$err->push(
						get_class($this),
						'ERR_DATA_TIME',
						$this->locale('ERR_DATA_TIME'),
						array(
							'col' => $field,
							'value' => $value
						),
						E_NOTICE,
						false
					);
				}
				break;
			
			case 'timestamp':
				settype($value, 'string');
				// make sure it's in the format yyyy-mm-ddThh:ii:ss
				$value = substr($value, 0, 10) . 'T' . substr($value, 11, 8);
				if (! $valid->isoDatetime($value)) {
					$err->push(
						get_class($this),
						'ERR_DATA_TIMESTAMP',
						$this->locale('ERR_DATA_TIMESTAMP'),
						array(
							'col' => $field,
							'value' => $value
						),
						E_NOTICE,
						false
					);
				}
				break;
			}
			
			
			// -------------------------------------------------------------
			// 
			// Content validation
			// 
			
			// add validation placeholder array if needed
			if (! isset($this->col[$field]['valid'])) {
				$this->col[$field]['valid'] = array();
			}
			
			// loop through each validation rule
			foreach ($this->col[$field]['valid'] as $args) {
				
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
					array($valid, $method),
					$args
				);
				
				// was it valid?
				if (! $result) {
					$err->push(
						get_class($this),
						'ERR_DATA',
						$text,
						array(
							'col' => $field,
							'value' => $value
						),
						E_NOTICE,
						false
					);
				}
				
			} // endforeach
			
		} // endforeach()
		
		
		// -------------------------------------------------------------
		// 
		// Done.
		// 
		
		if ($err->count() > 0) {
			// there were errors, add a warning error to the stack
			$err->push(
				get_class($this),
				'ERR_DATA',
				$this->locale('ERR_DATA'),
				array(),
				E_USER_WARNING,
				true // backtrace
			);
			return $err;
		}
	}
}
?>