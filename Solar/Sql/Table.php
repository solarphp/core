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
		'paging' => 10,
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
	* The default order when fetching rows.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $order = array('id');
	
	
	/**
	* 
	* The numer of rows per page when selecting.
	* 
	* @access protected
	* 
	* @var int
	* 
	*/
	
	protected $paging = 10;
	
	
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
	*         'autoinc' => (bool) auto-increment
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
		$this->paging($this->config['paging']);
		
		// perform column and index setup, then fix everything.
		$this->setup();
		$this->autoSetup();
		
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
	* @param string $key The property name.
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
	* Sets the number of rows per page.
	* 
	* @access public
	* 
	* @param int $val The number of rows per page.
	* 
	*/
	
	public function paging($val)
	{
		$this->paging = (int) $val;
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
		settype($data, 'array');
		
		// set defaults
		$data = array_merge($this->getDefault(), $data);
		
		// auto-add sequential values
		foreach ($this->col as $colname => $colinfo) {
			// does this column autoincrement, and is no data provided?
			if ($colinfo['autoinc'] && empty($data[$colname])) {
				$data[$colname] = $this->increment($colname);
			}
		}
		
		// add created/updated timestamps
		$now = date('Y-m-d\TH:i:s');
		
		if (empty($data['created'])) {
			$data['created'] = $now;
		}
		
		if (empty($data['updated'])) {
			$data['updated'] = $now;
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
			if ($this->col[$field]['primary']) {
				$retain[$field] = $data[$field];
				unset($data[$field]);
			}
		}
		
		// set the "updated" timestamp
		if (empty($data['updated'])) {
			$data['updated'] = date('Y-m-d\TH:i:s');
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
		// attempt the deletion
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
		
		// all columns from this table
		$select->from($this->name, array_keys($this->col));
		
		// conditions
		$select->multiWhere($where);
		
		// ordering
		$select->order($order);
		
		// by page
		$select->paging($this->paging);
		$select->limitPage($page);
		
		// fetch and return results
		return $select->fetch($type);
	}
	
	
	/**
	* 
	* Increments and returns the sequence value for a column.
	* 
	* @access public
	* 
	* @param string $name The column name.
	* 
	* @return int The next sequence number for the column.
	* 
	*/
	
	public function increment($name)
	{
		// only increment if auto-increment is set
		if (! empty($this->col[$name]['autoinc'])) {
			// table__column
			$seqname = $this->name . '__' . $name;
			$result = $this->sql->nextSequence($seqname);
			return $result;
		} else {
			return null;
		}
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
			// we shift off the front of the array as we go.
			// element 0 is the type (literal or callback),
			// element 1 is the literal (or callback name),
			// elements 2+ are any arguments for a callback.
			$type = array_shift($info['default']);
			switch ($type) {
			
			case 'callback':
				$func = array_shift($info['default']);
				$data[$name] = call_user_func_array($func, $info['default']);
				break;
			
			case 'literal':
				$data[$name] = array_shift($info['default']);
				break;
			
			default:
				$data[$name] = null;
			}
		}
		
		// don't send along the created/updated fields, they should
		// be auto-set on insert/update
		unset($data['created']);
		unset($data['updated']);
		
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

	protected final function autoSetup()
	{
		// a baseline column definition
		$basecol = array(
			'name'    => null,
			'type'    => null,
			'size'    => null,
			'scope'   => null,
			'primary' => false,
			'require' => false,
			'autoinc' => false,
			'default' => null,
			'valid'   => array(),
		);
		
		// auto-added columns and indexes
		$autocol = array();
		$autoidx = array();
		
		// auto-add an ID column and index for unique identification
		if (! array_key_exists('id', $this->col)) {
			$autocol['id'] = array(
				'type'    => 'int',
				'primary' => true,
				'require' => true,
				'autoinc' => true,
			);
			
			$autoidx['id'] = 'unique';
		}
		
		// auto-add a "created" column to track when created
		if (! array_key_exists('created', $this->col)) {
			$autocol['created'] = array(
				'type'    => 'timestamp',
				'default' => array('callback', 'date', 'Y-m-d\TH:i:s'),
			);
			
			$autoidx['created'] = 'normal';
		}
		
		// auto-add an "updated" column and index
		// to track when last updated
		if (! array_key_exists('updated', $this->col)) {
			$autocol['updated'] = array(
				'type'    => 'timestamp',
				'default' => array('callback', 'date', 'Y-m-d\TH:i:s'),
			);
			
			$autoidx['updated'] = 'normal';
		}
		
		// merge the auto-added items on top of the rest
		$this->col = array_merge($autocol, $this->col);
		$this->idx = array_merge($autoidx, $this->idx);
		
		// fix up each column to have a full set of info
		foreach ($this->col as $name => $info) {
		
			// fill in missing elements
			$info = array_merge($basecol, $info);
			
			// make sure there's a name
			$info['name'] = $name;
			
			// if 'valid' is not already an array, make it
			// one as a simple Solar_Valid call.
			if (! is_array($info['valid'])) {
				$info['valid'] = array(
					array(
						$info['valid'], // the method
						$this->locale(strtoupper("VALID_$name")) // validation message
					)
				);
			} else {
				// insert the validation message into the array
				foreach ($info['valid'] as $key => $val) {
					// shift the validation function off the top
					$func = array_shift($val);
					// add the validation message
					// after the function name
					array_unshift(
						$val,
						$func,
						$this->locale(strtoupper("VALID_$name"))
					);
					// save the new version of the validations
					$info['valid'][$key] = $val;
				}
			}
			
			
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
				Solar::locale('Solar_Sql', 'ERR_TABLE_NOT_CREATED'),
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
					Solar::locale('Solar_Sql', 'ERR_TABLE_NOT_CREATED'),
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
	* @todo Use $this->errorPush() instead of $err->push().
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
			
			
			// -------------------------------------------------------------
			// 
			// Recast first, then validate for column type
			// 
			
			$type = $this->col[$field]['type'];
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
						Solar::locale('Solar_Sql', 'ERR_DATA_MAXSIZE'),
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
						Solar::locale('Solar_Sql', 'ERR_DATA_INTRANGE'),
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
						Solar::locale('Solar_Sql', 'ERR_DATA_NUMRANGE'),
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
						Solar::locale('Solar_Sql', 'ERR_DATA_DATE'),
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
						Solar::locale('Solar_Sql', 'ERR_DATA_TIME'),
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
						Solar::locale('Solar_Sql', 'ERR_DATA_TIMESTAMP'),
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
			// Content validations
			// 
			
			// loop through each validation rule
			foreach ($this->col[$field]['valid'] as $args) {
				
				// the name of the Solar_Valid method
				$method = array_shift($args);
				
				// the error code and message to use
				// if an error is generated
				$code = 'VALID_' . strtoupper($field);
				$message = array_shift($args);
				if (empty($message)) {
					$message = $this->locale($code);
				}
				
				// validation config is now the remaining arguments,
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
						$code,
						$message,
						array(
							'col' => $field,
							'value' => $value
						),
						E_NOTICE,
						false
					);
				}
			} // endforeach
			
			
			// ---------------------------------------------------------
			// 
			// Retain the recasted and validated value ... ???
			// 
			
			$data[$field] = $value;
			
			
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
				Solar::locale('Solar_Sql', 'ERR_DATA'),
				array(),
				E_USER_WARNING,
				true // backtrace
			);
			return $err;
		}
	}
}
?>