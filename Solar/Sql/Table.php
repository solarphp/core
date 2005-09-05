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
* Needed for data validation.
*/
Solar::loadClass('Solar_Valid');

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
	*         'name'     => // (string) the colName, same as the key
	*         'type'     => // (string) char, varchar, date, etc
	*         'size'     => // (int) column size
	*         'scope'    => // (int) decimal places
	*         'label'    => // (string) Text label for forms and tables
	*         'valid'    => // (array) Solar_Valid methods and args
	*         'require'  => // (bool) is this a required column?
	*         'sequence' => // (string) use this auto-sequence
	*         'default'  => // (string|array) default value
	*         'primary'  => // (bool) is this part of the primary key?
	*      ),
	* );
	* </code>
	* 
	* @access protected
	* 
	* @var array
	* 
	* @see addCol()
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
	*/

	public function __construct($config = null)
	{
		// main construction
		parent::__construct($config);
		
		// perform column and index setup
		$this->setup();
		
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
		$data = array_merge($this->getcolDefault(), $data);
		
		// forcibly add sequential values
		foreach ($this->col as $field => $info) {
			// does this field use a sequence?
			if (! empty($info['sequence'])) {
				// yes, override any given values
				$data[$field] = $this->sql->nextcolSequence($info['sequence']);
			}
		}
		
		// validate and recast the data.
		$result = $this->autocolValid($data);
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
	* @param string $where An SQL WHERE clause limiting the updated rows.
	* 
	* @return mixed The updated data on success, Solar_Error object on failure.
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
		$result = $this->autocolValid($data);
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
	* Returns a data array with column keys and default values.
	* 
	* @access public
	* 
	* @return array
	* 
	*/
	
	public function getcolDefault()
	{
		$data = array();
		foreach ($this->col as $name => $info) {
			
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
	
	
	/**
	* 
	* Convenience method to fetch a series of rows.
	* 
	* @access public
	* 
	* @return array
	* 
	*/
	
	protected function _fetchList($where = null, $order = null, $page = null)
	{
		// selection tool
		$select = Solar::object('Solar_Sql_Select');
		
		// all non-CLOB columns
		foreach ($this->col as $name => $info) {
			if ($info['type'] != 'clob') {
				$select->cols($name);
			}
		}
			
		// from this table
		$select->from($this->name);
		
		// where ...
		$data = array();
		if (is_array($where) || is_object($where)) {
			foreach ((array) $where as $col => $val) {
				if (is_string($col)) {
					// ... column = :placeholder
					$select->where("$col = :$col");
					$data[$col] = $val;
				} else {
					// naked user-defined clause
					$select->where($val);
				}
			}
		} else {
			// ... user-specified clause
			$select->where($where);
		}
		
		// order by?
		$select->order($order);
		
		// return all results
		$select->fetch('all');
		
		// execute
		$result = $select->exec($data, $page);
		
		// done!
		return $result;
	}
	
	
	/**
	* 
	* Convenience method to fetch a single row.
	* 
	* @access public
	* 
	* @return array
	* 
	*/
	
	protected function _fetchItem($where = null, $order = null)
	{
		// selection tool
		$select = Solar::object('Solar_Sql_Select');
		
		// all columns including CLOBs
		$cols = array_keys($this->col);
		$select->cols($cols);
			
		// from this table
		$select->from($this->name);
		
		// where ...
		$data = array();
		if (is_array($where) || is_object($where)) {
			foreach ((array) $where as $col => $val) {
				if (is_string($col)) {
					// ... column = :placeholder
					$select->where("$col = :$col");
					$data[$col] = $val;
				} else {
					// naked user-defined clause
					$select->where($val);
				}
			}
		} else {
			// ... user-specified clause
			$select->where($where);
		}
		
		// order by?
		$select->order($order);
		
		// return all results
		$select->fetch('row');
		
		// execute
		$result = $select->exec($data, $page);
		
		// done!
		return $result;
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
	* Adds a basic column definition and auto-labels it.
	* 
	* @access protected
	* 
	* @param string $name The column name.
	* 
	* @param string $type The column name.
	* 
	* @param int $size The column size or width.
	* 
	* @param int $scope The decimal scope for numeric types.
	* 
	* @return void
	* 
	*/

	protected function colDefine($name, $type, $size = null, $scope = null)
	{
		$locale_key = strtoupper("LABEL_$name");
		$label = $this->locale($locale_key);
		$this->col[$name] = array(
			'name'     => $name,
			'type'     => $type,
			'size'     => $size,
			'scope'    => $scope,
			'label'    => $label,
			'require'  => false,
			'sequence' => null,
			'default'  => false,
			'primary'  => false,
			'foreign'  => array(),
			'valid' => array(),
		);
	}
	
	
	/**
	* 
	* Sets the 'require' flag for a column.
	* 
	* When 'require' is true, the column value cannot be NULL.
	* 
	* @access protected
	* 
	* @param string $name The column name.
	* 
	* @return void
	* 
	*/

	protected function colRequire($name)
	{
		/** @todo Throw error if column doesn't exist */
		$this->col[$name]['require'] = true;
	}
	
	
	/**
	* 
	* Sets the sequence name for this column.
	* 
	* Use this to auto-set a sequence value for the column on insert().
	* 
	* @access protected
	* 
	* @param string $name The column name.
	* 
	* @param string $seq The sequence name; if empty, defaults to the table
	* name, two underscores, and the column name ('table__col');
	* 
	* @return void
	* 
	*/
	
	protected function colSequence($name, $seq = null)
	{
		/** @todo Throw error if column doesn't exist */
		if (empty($seq)) {
			$seq = $this->name . '__' . $name;
		}
		$this->col[$name]['sequence'] = $seq;
	}
	
	
	/**
	* 
	* Sets the default value for a column.
	* 
	* If a string, use the literal value of the string; if an array, use
	* element-0 as a callback for call_user_func_array().
	* 
	* @access protected
	* 
	* @param string $name The column name.
	* 
	* @param string|array $value The default literal value (string) or callback
	* to generate a value (array).
	* 
	* @return void
	* 
	*/

	protected function colDefault($name, $type, $value)
	{
		/** @todo Throw error if column doesn't exist */
		$args = func_get_args();
		
		// drop the first two arguments
		array_shift($args); // $name
		array_shift($args); // $type
		
		$this->col[$name]['default'] = array(
			'type' => $type,
			'args' => $args
		);
	}
	
	
	/**
	* 
	* Add validation (and validation messages) for column data.
	* 
	* @access protected
	* 
	* @param string $name The column name.
	* 
	* @param string $method A Solar_Valid method name.
	* 
	* @param string $locale_key The locale-string key for an error message
	* if validation fails.
	* 
	* @return void
	* 
	*/
	
	protected function colValid($name, $method)
	{
		/** @todo Throw error if column doesn't exist */
		$args = func_get_args();
		
		// drop the first two arguments
		array_shift($args); // $name
		array_shift($args); // $method
		
		// get a translated validation message
		$key = strtoupper("VALID_$name");
		$message = $this->locale($key);
		
		// put the message on top of the array
		array_unshift($args, $message);
		
		// now put the method on top of that
		array_unshift($args, $method);
		
		// and add the full validation arguments
		$this->col[$name]['valid'][] = $args;
	}
	
	
	/**
	* 
	* Sets/resets the 'primary' flag for one or more columns.
	* 
	* Use this to identify primary keys for a table.
	* 
	* One column:  $this->primary('col_1');
	* 
	* Compsite: $this->primary('col_1', 'col_2', 'col_3');
	* 
	* @access protected
	* 
	* @return void
	* 
	*/

	protected function primary()
	{
		/** @todo Throw error if column doesn't exist */
		$args = func_get_args();
		foreach ($args as $name) {
			$this->col[$name]['primary'] = true;
		}
	}
	
	
	/**
	* 
	* Adds a "normal" index.
	* 
	* Index a column:  $this->index('col_1');
	* 
	* Index a column as a new name: $this->index('idx_name', 'col_1');
	* 
	* Composite index: $this->index('comp_idx_name', 'col_1', 'col_2', ...);
	* 
	* @access protected
	* 
	* @param string $name The index name.
	* 
	* @param string|array $cols The colDefine(s) on which to index.  If
	* none, the index name doubles as the column name to index on.
	* 
	* @return void
	* 
	*/

	protected function index($name)
	{
		$cols = func_get_args();
		$name = array_shift($cols);
		
		if (empty($cols)) {
			$cols = $name;
		}
		
		$this->idx[$name] = array(
			'name' => $name,
			'type' => 'normal',
			'cols' => (array) $cols
		);
	}
	
	
	/**
	* 
	* Adds a "unique" index.
	* 
	* Index a column:  $this->unique('col_1');
	* 
	* Index a column as a new name: $this->unique('idx_name', 'col_1');
	* 
	* Composite index: $this->unique('comp_idx_name', 'col_1', 'col_2', ...);
	* 
	* @access protected
	* 
	* @param string $name The index name.
	* 
	* @param string|array $cols The colDefine(s) on which to index.  If
	* none, the index name doubles as the column name to index on.
	* 
	* @return void
	* 
	*/

	protected function unique($name)
	{
		$cols = func_get_args();
		$name = array_shift($cols);
		
		if (empty($cols)) {
			$cols = $name;
		}
		
		$this->idx[$name] = array(
			'name' => $name,
			'type' => 'unique',
			'cols' => (array) $cols
		);
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

	protected function autoCreate()
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
	
	protected function autocolValid(&$data)
	{
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
				if (! Solar_Valid::inScope($value, $size, $scope)) {
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
				if (! Solar_Valid::isoDate($value)) {
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
				if (! Solar_Valid::isoTime($value)) {
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
				if (! Solar_Valid::isoDatetime($value)) {
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
					array('Solar_Valid', $method),
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