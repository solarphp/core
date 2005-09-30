<?php
/**
* 
* Class for connecting to SQL databases and performing standard operations.
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
* Base class for additional SQL driver information.
*/

require_once 'Solar/Sql/Driver.php';


/**
* 
* Class for connecting to SQL databases and performing common operations.
* 
* Example usage:
* 
* <code>
* 
* $opts = array(
* 	'class' => 'Solar_Sql_Driver_Mysql',
* 	'host' => '127.0.0.1',
* 	'user' => 'pmjones',
* 	'pass' => '********',
* 	'name' => 'test'
* );
* 
* $sql = Solar::object('Solar_Sql', $opts);
* 
* // a command with placeholders
* $stmt = "SELECT * FROM some_table WHERE date >= :first AND date <= :last";
* 
* // data for the placeholders
* $data = array(
* 	'first' => '1970-01-01',
* 	'last'  => '1979-12-31'
* )
* 
* // retrieve a result set from the bound command and data
* $result = $sql->exec($stmt, $data);
* 
* // fetch the first row of the result set and free the result
* $row = $result->fetch();
* unset($result);
* 
* // equivalent:
* $row = $sql->fetchRow($stmt, $data);
* 
* </code>
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Sql
* 
*/


class Solar_Sql extends Solar_Base {
	
	
	/**
	* 
	* User-provided configuration.
	* 
	* Keys are:
	* 
	* class => (string) Driver information class, e.g. 'Solar_Sql_Driver_Mysql'.
	* 
	* host => (string) Host specification (typically 'localhost').
	* 
	* port => (string) Port number for the host name.
	* 
	* user => (string) Connect to the database as this username.
	* 
	* pass => (string) Password associated with the username.
	* 
	* name => (string) Database name (or file path, or TNS name).
	* 
	* mode => (string) For SQLite, an octal file mode.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $config = array(
		'class' => null,
		'host' => '127.0.0.1',
		'port' => null,
		'user' => null,
		'pass' => null,
		'name' => null,
		'mode' => null,
	);
	
	
	/**
	* 
	* Object to customize for a specific RDBMS.
	* 
	* @access protected
	* 
	* @var object
	*
	*/
	
	protected $driver = null;
	
	
	/**
	* 
	* Max identifier lengths for table, column, and index names.
	* 
	* The total length cannot exceed 63 (the Postgres limit).
	* 
	* Reserve 3 chars for suffixes ("__i" for indexes, "__s" for
	* sequences).
	* 
	* Reserve 2 chars for table__index separator (again, because
	* Postgres needs unique names for indexes even on different tables).
	* 
	* This leaves 58 characters to split between table name and col/idx
	* name.  Figure tables need more "space", so they get 30 and
	* tables/indexes get 28.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $len = array(
		'tbl' => 30,
		'col' => 28,
		'idx' => 28
	);
	
	
	/**
	* 
	* A portable database object for accessing the RDBMS.
	* 
	* @access protected
	* 
	* @var object
	*
	*/
	
	protected $pdo = null;
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	* @param array $config An array of configuration options.
	* 
	*/
	
	public function __construct($config = null)
	{
		// basic construction
		parent::__construct($config);
		
		// create the driver-info object
		$opts = $this->config;
		unset($opts['class']);
		$opts['sql'] = $this;
		$this->driver = Solar::object($this->config['class'], $opts);
	}
	
	
	/**
	* 
	* Prepares and executes an SQL statement with bound data.
	* 
	* @access public
	* 
	* @param string $stmt The text of the SQL statement, with
	* placeholders.
	* 
	* @param array $data An associative array of data to bind to the
	* placeholders.
	* 
	* @return object A PDOStatement object.
	* 
	* @todo Catch exceptions and return as Solar_Error objects.
	* 
	*/
	
	public function exec($stmt, $data = array())
	{
		// connect to the database if needed
		$this->connect();
		
		// force the bound data to be an array
		settype($data, 'array');
		
		// prepare the statement
		try {
			$obj = $this->pdo->prepare($stmt);
		} catch (Exception $e) {
			$err = $this->errorException($e);
			return $err;
		}
		
		// execute with bound data
		try {
			$obj->execute($data);
		} catch (Exception $e) {
			$err = $this->errorException($e);
			return $err;
		}
		
		// return the results embedded in the prepared statement object
		return $obj;
	}
	
	/**
	* 
	* Inserts a row of data into a table.
	* 
	* @access public
	* 
	* @param string $table The table to insert data into.
	* 
	* @param array $data An associative array where the key is the column
	* name and the value is the value to insert for that column.
	* 
	* @return mixed A Solar_Error on error.
	* 
	*/
	
	public function insert($table, $data)
	{
		// the base statement
		$stmt = "INSERT INTO $table ";
		
		// field names come from the array keys
		$fields = array_keys($data);
		
		// add field names themselves
		$stmt .= '(' . implode(', ', $fields) . ') ';
		
		// add value placeholders
		$stmt .= 'VALUES (:' . implode(', :', $fields) . ')';
		
		// execute the statement
		return $this->exec($stmt, $data);
	}
	
	
	/**
	* 
	* Updates a table with specified data based on a WHERE clause.
	* 
	* @access public
	* 
	* @param string $table The table to udpate.
	* 
	* @param array $data An associative array where the key is the column
	* name and the value is the value to use for that column.
	* 
	* @param string $where The SQL WHERE clause to limit which rows are
	* updated.
	* 
	* @return mixed A Solar_Error on error.
	* 
	*/
	
	public function update($table, $data, $where)
	{
		// the base statement
		$stmt = "UPDATE $table SET ";
		
		// add "col = :col" pairs to the statement
		$tmp = array();
		foreach ($data as $col => $val) {
			$tmp[] = "$col = :$col";
		}
		$stmt .= implode(', ', $tmp);
		
		// add the where clause, execute, and return
		$stmt .= " WHERE $where";
		return $this->exec($stmt, $data);
	}
	
	
	/**
	* 
	* Deletes rows from the table based on a WHERE clause.
	* 
	* @access public
	* 
	* @param string $table The table to delete from.
	* 
	* @param string $where The SQL WHERE clause to limit which rows are
	* deleted.
	* 
	* @return mixed A Solar_Error on error.
	* 
	*/
	
	public function delete($table, $where)
	{
		return $this->exec("DELETE FROM $table WHERE $where");
	}
	
	
	/**
	* 
	* Select rows from the database.
	* 
	* @access public
	* 
	* @param string $return How to return the results: all, assoc, col,
	* one, pair, row, result (the default), statement (to just get the
	* statement), or a class name to create with the result.
	* 
	* @param array|string $spec An array of component parts for a
	* SELECT, or a literal query string (SELECT or non-select).
	* 
	* @param array $data An associative array of data to bind into the
	* SELECT statement.
	* 
	* @return mixed A Solar_Error on error, or the query results for the
	* return type requested.
	* 
	*/
	
	public function select($return, $spec, $data = array())
	{
		// build the statement from its component parts if needed
		if (is_array($spec)) {
			$stmt = $this->driver->buildSelect($spec);
		} else {
			$stmt = $spec;
		}
		
		// are we just returning the statement?
		if (strtolower($return) == 'statement') {
			return $stmt;
		}
		
		// execute and get the PDOStatement result object
		$result = $this->exec($stmt, $data);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// return data based on the select type
		switch (strtolower($return)) {
		
		// capture all rows
		case 'all':
			$data = $result->fetchAll(PDO_FETCH_ASSOC);
			break;
			
		// capture data as key-value pairs where the first column
		// is the key and the second column is the value
		case 'assoc':
			$data = array();
			while ($row = $result->fetch(PDO_FETCH_ASSOC)) {
				$key = array_shift($row);
				$data[$key] = $row;
			}
			break;
		
		// capture the first col of every row.
		case 'col':
			$data = array();
			while ($col = $result->fetchColumn(0)) {
				$data[] = $col;
			}
			break;
			
		// capture the first column of the first row
		case 'one':
			$data = $result->fetchColumn(0);
			break;
		
		// capture data as key-value pairs where the first column
		// is the key and the second column is the value
		case 'pair':
			$data = array();
			while ($row = $result->fetch(PDO_FETCH_NUM)) {
				$data[$row[0]] = $row[1];
			}
			break;
		
		// the PDOStatement result object
		case 'pdo':
			$data = $result;
			break;
			
		// a Solar_Sql_Result object
		case 'result':
			$data = Solar::object('Solar_Sql_Result');
			$data->PDOStatement = $result;
			break;
		
		// capture the first row
		case 'row':
			$data = $result->fetch(PDO_FETCH_ASSOC);
			break;
		
		// create a new object and put the result into it
		default:
			$data = Solar::object(
				$return,
				array('PDOStatement' => $result)
			);
			break;
		}
		
		// done!
		return $data;
	}
	
	
	// -----------------------------------------------------------------
	// 
	// Sequences
	// 
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Create a sequence in the database.
	* 
	* @access public
	* 
	* @param string $name The sequence name to create.
	* 
	* @param string $start The starting sequence number.
	* 
	* @return mixed
	* 
	* @todo Check name length.
	* 
	*/
	
	public function createSequence($name, $start = 1)
	{
		$name .= '__s';
		$result = $this->driver->createSequence($this, $name, $start);
		return $result;
	}
	
	
	/**
	* 
	* Drop a sequence from the database.
	* 
	* @access public
	* 
	* @param string $name The sequence name to drop.
	* 
	* @return mixed
	* 
	*/
	
	public function dropSequence($name)
	{
		$name .= '__s';
		$result = $this->driver->dropSequence($this, $name);
		return $result;
	}
	
	
	/**
	* 
	* Gets the next number in a sequence number.
	* 
	* Creates the sequence if it does not exist.
	* 
	* @access public
	* 
	* @param string &$name The sequence name.
	* 
	* @return int The next sequence number.
	* 
	*/
	
	public function nextSequence($name)
	{
		$name .= '__s';
		$result = $this->driver->nextSequence($this, $name);
		return $result;
	}
	
	
	// -----------------------------------------------------------------
	// 
	// Table, column, and index management
	// 
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Creates a table.
	* 
	* The $cols parameter should be in this format:
	* 
	* $cols = array(
	*   'fieldOne' => array(
	*     'type'    => bool|char|int|etc,
	*     'size'    => total length for char|varchar|numeric
	*     'scope'   => decimal places for numeric
	*     'require' => true|false,
	*   ),
	*   'fieldTwo' => array(...)
	* );
	* 
	* @access public
	* 
	* @param string $table The name of the table to create.
	* 
	* @param array $cols Array of columns to create.
	* 
	* @return mixed An SQL string, or a Solar_Error.
	* 
	*/
	
	public function createTable($table, $cols)
	{
		// table name can only be so many chars
		$len = strlen($table);
		if ($len < 1 || $len > $this->len['tbl']) {
			return $this->error(
				'ERR_TABLE_LEN',
				array('table' => $table),
				E_USER_WARNING
			);
		}
		
		// table name must be a valid word, and cannot end in
		// "__s" (this is to prevent sequence table collisions)
		if (! $this->validWord($table) || substr($table, -3) == "__s") {
			return $this->error(
				'ERR_TABLE_WORD',
				array('table' => $table),
				E_USER_WARNING
			);
		}
		
		// array of column definitions
		$coldef = array();
		
		// use this to stack errors when creating definitions
		$err = Solar::object('Solar_Error');
		
		// loop through each column and get its definition
		foreach ($cols as $name => $info) {
			$result = $this->buildColDef($name, $info);
			if (Solar::isError($result)) {
				$err->push($result);
			} else {
				$coldef[] = "$name $result";
			}
		}
		
		// were there errors?
		if ($err->count() > 0) {
			return $err;
		} else {
			// no errors, execute and return
			$cols = implode(",\n\t", $coldef);
			$stmt = "CREATE TABLE $table (\n$cols\n)";
			$result = $this->exec($stmt);
			return $result;
		}
	}
	
	
	/**
	* 
	* Drops a table from the database.
	* 
	* @access public
	* 
	* @param string $table The table name.
	* 
	* @return mixed
	* 
	*/
	
	public function dropTable($table)
	{
		return $this->exec("DROP TABLE $table");
	}
	
	
	/**
	* 
	* Adds a column to a table in the database.
	* 
	* @access public
	* 
	* @param string $table The table name.
	* 
	* @param string $name The column name to add.
	* 
	* @param array $info Information about the column.
	* 
	* @return mixed
	* 
	*/
	
	public function addColumn($table, $name, $info)
	{
		$coldef = $this->buildColDef($name, $info);
		if (Solar::isError($coldef)) {
			return $coldef;
		} else {
			$stmt = "ALTER TABLE $table ADD COLUMN $coldef";
			return $this->exec($stmt);
		}
	}
	
	
	/**
	* 
	* Drops a columns from a table in the database.
	* 
	* @access public
	* 
	* @param string $table The table name.
	* 
	* @param string $name The column name to drop.
	* 
	* @return mixed
	* 
	*/
	
	public function dropColumn($table, $name)
	{
		return $this->exec("ALTER TABLE $table DROP COLUMN $name");
	}
	
	
	/**
	* 
	* Creates an index on a table.
	* 
	* The $info parameter should be in this format:
	* 
	* $info = array('type', 'col'); // single-col
	* 
	* $info = array('type', array('col', 'col', 'col')), // multi-col
	* 
	* $info = 'type'; // shorthand for single-col named for $name
	* 
	* The type may be 'normal' or 'unique'.
	* 
	* Indexes are automatically renamed to "tablename__indexname__i".
	* 
	* @access public
	* 
	* @param string $table The name of the table for the index (1-30 chars).
	* 
	* @param string $name The name of the index (1-27 chars).
	* 
	* @param string|array $info Information about the index.
	* 
	* @return mixed An SQL string, or a Solar_Error.
	* 
	*/
	
	public function createIndex($table, $name, $info)
	{
		// check the table name length
		$len = strlen($table);
		if ($len < 1 || $len > $this->len['tbl']) {
			return $this->error(
				'ERR_TABLE_LEN',
				array('table' => $table),
				E_USER_WARNING
			);
		}
		
		// check the index name length
		$len = strlen($name);
		if ($len < 1 || $len > $this->len['idx']) {
			return $this->error(
				'ERR_IDX_LEN',
				array('table' => $table, 'name' => $name),
				E_USER_WARNING
			);
		}
		
		// build a definition statement
		$stmt = $this->buildIdxDef($table, $name, $info);
		
		// were there errors?
		if (Solar::isError($stmt)) {
			// yes, return the error object
			return $stmt;
		} else {
			// no errors, execute and return
			return $this->exec($stmt);
		}
	}
	
	
	/**
	* 
	* Drops an index from a table in the database.
	* 
	* @access public
	* 
	* @param string $table The table name.
	* 
	* @param string $name The index name to drop.
	* 
	* @return mixed
	* 
	*/
	
	public function dropIndex($table, $name)
	{
		return $this->exec("DROP INDEX $name ON $table");
	}
	
	
	// -----------------------------------------------------------------
	// 
	// Miscellaneous
	// 
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Safely quotes a value for an SQL statement.
	* 
	* Recursively quotes array values (but not their keys).
	* 
	* @access public
	* 
	* @param mixed $val The value to quote.
	* 
	* @return mixed An SQL-safe quoted value.
	* 
	*/
	
	public function quote($val)
	{
		$this->connect();
		if (is_array($val)) {
			// recursively quote array values
			foreach ($val as $k => $v) {
				$val[$k] = $this->pdo->quote($v);
			}
		} else {
			$this->pdo->quote($val);
		}
		
		return $val;
	}
	
	
	/**
	* 
	* Returns a list of table names in the database.
	* 
	* @access public
	* 
	* @return array
	* 
	*/
	
	public function listTables()
	{
		return $this->driver->listTables($this);
	}
	
	
	// -----------------------------------------------------------------
	// 
	// Support
	// 
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Creates a PDO object and connects to the database.
	* 
	* @access protected
	* 
	* @return void
	* 
	*/
	
	protected function connect()
	{
		// if we already have a PDO object, no need to re-connect.
		if ($this->pdo) {
			return;
		}
		
		// build a DSN
		$dsn = $this->driver->dsn();
		
		// create PDO object
		try {
		
			$this->pdo = new PDO(
				$dsn,
				$this->config['user'],
				$this->config['pass']
			);
			
			// always autocommit
			$this->pdo->setAttribute(PDO_ATTR_AUTOCOMMIT, true);
			
			// force names to lower case
			$this->pdo->setAttribute(PDO_ATTR_CASE, PDO_CASE_LOWER);
			
			// always use exceptions.
			$this->pdo->setAttribute(PDO_ATTR_ERRMODE,
				PDO_ERRMODE_EXCEPTION);
			
		} catch (Exception $e) {
			$err = $this->errorException($e);
			return $err;
		}
	}
	
	
	/**
	*
	* Builds a column definition string.
	* 
	* The $info parameter should be in this format:
	* 
	* $info = array(
	*   'type'    => bool|char|int|etc,
	*   'size'    => total length for char|varchar|numeric
	*   'scope'   => decimal places for numeric
	*   'require' => true|false,
	* );
	* 
	* @access public
	* 
	* @param string $column The column name.
	* 
	* @param array $info The column information.
	* 
	*/
	
	protected function buildColDef($name, $info)
	{
		// validate column name length
		$len = strlen($name);
		if ($len < 1 || $len > $this->len['col']) {
			return $this->error(
				'ERR_COL_LEN',
				array('name' => $name),
				E_USER_WARNING,
				false
			);
		}
		
		// column name must be a valid word
		if (! $this->validWord($name)) {
			return $this->error(
				'ERR_COL_WORD',
				array('name' => $name),
				E_USER_WARNING,
				false
			);
		}
		
		// set default values for these variables
		$tmp = array(
			'type'	   => null,
			'size'	   => null,
			'scope'	   => null,
			'require'  => null, // true means NOT NULL, false means NULL
		);
		
		$info = array_merge($tmp, $info);
		extract($info); // see array keys, above
		
		// force values
		$name    = trim(strtolower($name));
		$type    = strtolower(trim($type));
		$size    = (int) $size;
		$scope   = (int) $scope;
		$require = (bool) $require;
		
		// is it a recognized column type?
		$native = $this->driver->nativeColTypes();
		if (! array_key_exists($type, $native)) {
			return $this->error(
				'ERR_COL_TYPE',
				array('name' => $name, 'type' => $type),
				E_USER_WARNING,
				false
			);
		}
		
		// basic declaration string
		switch ($type) {
		
		case 'char':
		case 'varchar':
			// does it have a valid size?
			if ($size < 1 || $size > 255) {
				return $this->error(
					'ERR_COL_SIZE',
					array('name' => $name, 'size' => $size),
					E_USER_WARNING,
					false
				);
			} else {
				// replace the 'size' placeholder
				$coldef = str_replace(':size', $size, $native[$type]);
			}
			break;
		
		case 'numeric':
		
			if ($size < 1 || $size > 255) {
				return $this->error(
					'ERR_COL_SIZE',
					array('name' => $name, 'size' => $size, 'scope' => $scope),
					E_USER_WARNING,
					false
				);
			}
			
			if ($scope < 0 || $scope > $size) {
				return $this->error(
					'ERR_COL_SCOPE',
					array('name' => $name, 'size' => $size, 'scope' => $scope),
					E_USER_WARNING,
					false
				);
			}
			
			// replace the 'size' and 'scope' placeholders
			$coldef = str_replace(
				array(':size', ':scope'),
				array($size, $scope),
				$native[$type]
			);
			
			break;
		
		default:
			$coldef = $native[$type];
			break;
		
		}
		
		// set the "NULL"/"NOT NULL" portion
		$coldef .= ($require) ? ' NOT NULL' : ' NULL';
		
		// done
		return $coldef;
	}
	
	
	/**
	*
	* Builds an index creation string.
	* 
	* @access protected
	* 
	* @param string $column The column name.
	* 
	* @param array $info The column information.
	* 
	*/
	
	protected function buildIdxDef($table, $name, $info)
	{
		// we prefix all index names with the table name,
		// and suffix all index names with '__i'.  this
		// is to soothe PostgreSQL, which demands that index
		// names not collide, even when they indexes are on
		// different tables.
		$fullname = $table . '__' . $name . '__i';
		
		// build up the index information for type and columns
		$type = null;
		$cols = null;
		if (is_string($info)) {
		
			// shorthand for index names: colname => index_type
			$type = trim($info);
			$cols = trim($name);
			
		} elseif (is_array($info)) {
		
			// longhand: index_name => array('type' => ..., 'cols' => ...)
			$type = (isset($info['type'])) ? $info['type'] : 'normal';
			$cols = (isset($info['cols'])) ? $info['cols'] : null;
			
		}
		
		// are there any columns for the index?
		if (! $cols) {
			return $this->error(
				'ERR_IDX_COLS',
				array('table' => $table, 'name' => $name),
				E_USER_WARNING
			);
		}
		
		// create a string of column names
		$list = implode(', ', (array) $cols);
		
		// create index entry
		if ($type == 'unique') {
			return "CREATE UNIQUE INDEX $fullname ON $table ($list)";
		} elseif ($type == 'normal') {
			return "CREATE INDEX $fullname ON $table ($list)";
		} else {
			return $this->error(
				'ERR_IDX_TYPE',
				array('table' => $table, 'name' => $name, 'type' => $type),
				E_USER_WARNING
			);
		}
	}
	
	
	/**
	* 
	* Check if a table, column, or index name is a valid word.
	* 
	* @access protected
	* 
	* @param string $word The word to check.
	* 
	* @return bool True if valid, false if not.
	* 
	*/
	
	protected function validWord($word)
	{
		static $reserved;
		if (! isset($reserved)) {
			$reserved = Solar::object('Solar_Sql_Reserved');
		}
		
		// is it a reserved word?
		if (in_array(strtoupper($word), $reserved->words)) {
			return false;
		}
		
		// only a-z, 0-9, and _ are allowed in words.
		// must start with a letter, not a number or underscore.
		if (! preg_match('/^[a-z][a-z0-9_]*$/', $word)) {
			return false;
		}
		
		// must not have two or more underscores in a row
		if (strpos($word, '__') !== false) {
			return false;
		}
		
		// guess it's OK
		return true;
	}
}
?>