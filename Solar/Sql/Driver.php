<?php

/**
* 
* Abstract class for connecting to SQL databases.
* 
* @category Solar
* 
* @package Solar_Sql
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
* Abstract class for connecting to SQL databases.
* 
* @category Solar
* 
* @package Solar_Sql
* 
*/

abstract class Solar_Sql_Driver extends Solar_Base {
	
	/**
	* 
	* User-provided configuration.
	* 
	* Keys are:
	* 
	* host  => (string) Host specification (typically 'localhost').
	* 
	* port  => (string) Port number for the host name.
	* 
	* user  => (string) Connect to the database as this username.
	* 
	* pass  => (string) Password associated with the username.
	* 
	* name  => (string) Database name (or file path, or TNS name).
	* 
	* mode  => (string) For SQLite, an octal file mode.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'locale' => 'Solar/Sql/Driver/Locale/',
		'host'   => null,
		'port'   => null,
		'user'   => null,
		'pass'   => null,
		'name'   => null,
		'mode'   => null,
	);
	
	
	/**
	* 
	* Map of Solar generic column types to RDBMS native declarations.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $native = array(
        'bool'      => null, // PHP is_bool()
        'char'      => null, 
        'varchar'   => null, 
        'smallint'  => null,
        'int'       => null, // PHP is_int()
        'bigint'    => null,
        'numeric'   => null, // PHP is_numeric(), treated as fixed-place decimal
        'float'     => null, // PHP is_float(), treated as double decimal ... how big is a PHP float, anyway?
        'clob'      => null,
        'date'      => null,
        'time'      => null,
        'timestamp' => null
	);
	
	
	/**
	* 
	* Database connection resource.
	* 
	* @access protected
	* 
	* @var resource
	* 
	*/
	
	protected $conn = null;
	
	
	/**
	* 
	* Max word lengths for table, column, and index names.
	* 
	* @access protected
	* 
	* @var resource
	* 
	*/
	
	protected $len = array(
		'tbl' => 30,
		'col' => 27,
		'idx' => 27
	);
	
	
	/**
	* 
	* Provides the proper escaping for enquoted values.
	* 
	* @access public
	* 
	* @param mixed $val
	* 
	* @return string An SQL-safe quoted value.
	* 
	*/
	
	public function escape($val)
	{
	}
	
	
	/**
	* 
	* Executes an SQL statement and returns a result object.
	* 
	* @access public
	* 
	* @param string $stmt The SQL statement to execute.
	* 
	* @param int $count The number of records to SELECT.
	* 
	* @param int $offset The number of records to skip on SELECT.
	* 
	* @return object A Solar_Sql_Result object.
	* 
	*/
	
	public function exec($stmt, $count = 0, $offset = 0)
	{
		// 1. Re-select the proper database if needed (Fbsql, Mssql, Mysql).
		// 
		// 2. Execute the query and get a result.  Emulate autocommit if
		// needed (Mssql).
		// 
		// 3. Process the result:
		// 
		// 3a. If a resource, return a Solar_SQL object.
		// 
		// 3b. If false, return a Solar_Error object.
		// 
		// 3c. Otherwise, return the result value itself.
	}
	
	
	/**
	* 
	* Fetches the next row from a result resource as an associative array.
	* 
	* @access public
	* 
	* @param resource &$rsrc An SQL result resource.
	* 
	* @return array|bool An associative array of the fetched row,
	* or boolean false when no more rows are available.
	* 
	*/
	
	public static function fetch(&$rsrc)
	{
	}
	
	
	/**
	* 
	* Fetches the next row from a result resource as a numeric array.
	* 
	* @access public
	* 
	* @param resource &$rsrc An SQL result resource.
	* 
	* @return array|bool A numeric array of the fetched row,
	* or boolean false when no more rows are available.
	* 
	*/
	
	public static function fetchNum(&$rsrc)
	{
	}
	
	
	/**
	* 
	* Frees a result resource.
	* 
	* @access public
	* 
	* @param resource &$rsrc An SQL result resource.
	* 
	* @return void
	* 
	*/
	
	public static function free(&$rsrc)
	{
	}
	
	
	/**
	* 
	* Adds a LIMIT clause (or equivalent) to an SQL statement in-place.
	* 
	* This method is dumb; it adds the clause to any kind of statement.
	* You should not call it directly; use exec() with the optional $count
	* and $offset parameters instead.
	* 
	* The code presented here works for Mysql, Pgsql, and Sqlite.
	* 
	* @access protected
	* 
	* @param string &$stmt The SQL statement to modify.
	* 
	* @param int $count The number of records to SELECT.
	* 
	* @param int $offset The number of records to skip on SELECT.
	* 
	* @return void
	* 
	*/
	
	public function limit(&$stmt, $count = 0, $offset = 0)
	{
	}
	
	
	/**
	* 
	* Creates a sequence, optionally starting at a certain number.
	* 
	* @access public
	* 
	* @param string $name The sequence name to create.
	* 
	* @param int $start The first sequence number to return.
	* 
	* @return void
	* 
	*/
	
	public function createSequence($name, $start = 1)
	{
	}
	
	
	/**
	* 
	* Drops a sequence.
	* 
	* @access public
	* 
	* @param string $name The sequence name to drop.
	* 
	* @return void
	* 
	*/
	
	public function dropSequence($name)
	{
	}
	
	/**
	* 
	* Gets the next sequence number; creates the sequence if needed.
	* 
	* Technically, you only need one sequence for your entire database;
	* the number itself is not important, only that it is unique.
	* 
	* @access public
	* 
	* @param string $name The sequence name to increment.
	* 
	* @return int The next sequence number.
	* 
	*/
	
	public function nextSequence($name)
	{
	}
	
	
	/**
	* 
	* Returns the SQL statement to get a list of database tables.
	* 
	* @access public
	* 
	* @return array A sequential array of table names in the database.
	* 
	*/
	
	public function listTables()
	{
	}
	
	
	/**
	*
	* Get an SQL column declarartion string.
	* 
	* The $info parameter should be in this format:
	* 
	* $info = array(
	*   'type'    => bool|char|int|etc,
	*   'size'    => total length for char|varchar|numeric
	*   'scope'   => decimal places for numeric
	*   'require' => true|false,
	*   'default' => default value
	* );
	* 
	* @access public
	* 
	* @param string $column The column name.
	* 
	* @param array $info The column information.
	* 
	*/
	
	public function columnDeclaration($name, $info)
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
			'default'  => null
		);
		
		$info = array_merge($tmp, $info);
		extract($info); // see array keys, above
		
		// force values
		$name    = trim(strtolower($name));
		$type    = strtolower(trim($type));
		$size    = (int) $size;
		$scope   = (int) $scope;
		$require = (bool) $require;
		
		// if the default is an array or object, don't allow it.
		if (is_array($default) || is_object($default)) {
			$default = null;
		}
		
		// is it a recognized column type?
		if (! in_array($type, array_keys($this->native))) {
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
				$declare = $this->native[$type] . "($size)";
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
			
			$declare = $this->native[$type] . "($size,$scope)";
			break;
		
		default:
			$declare = $this->native[$type];
			break;
		
		}
		
		// set the "NULL"/"NOT NULL" portion
		$declare .= ($require) ? ' NOT NULL' : ' NULL';
		
		// set the "DEFAULT" portion
		$declare .= ($default) ? " DEFAULT '$default'" : '';
		
		// done
		return $declare;
	}
	
	
	/**
	* 
	* Returns the SQL statement to create a table and its columns.
	* 
	* The $cols parameter should be in this format:
	* 
	* $cols = array(
	*   'fieldOne' => array(
	*     'type'    => bool|char|int|etc,
	*     'size'    => total length for char|varchar|numeric
	*     'scope'   => decimal places for numeric
	*     'require' => true|false,
	*     'default' => default value
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
		// "__seq" (this is to prevent sequence table collisions)
		if (! $this->validWord($table) || substr($table, -5) == "__seq") {
			return $this->error(
				'ERR_TABLE_WORD',
				array('table' => $table),
				E_USER_WARNING
			);
		}
		
		// array of column declarations
		$declare = array();
		
		// use this to stack errors when creating declarations
		$err = Solar::object('Solar_Error');
		
		// loop through each column and get its declaration
		foreach ($cols as $name => $info) {
			$result = $this->columnDeclaration($name, $info);
			if (Solar::isError($result)) {
				$err->push(__CLASS__, $result);
			} else {
				$declare[] = "$name $result";
			}
		}
		
		// were there errors?
		if ($err->count() > 0) {
			return $err;
		} else {
			// no errors, return a statement
			$cols = implode(",\n\t", $declare);
			return "CREATE TABLE $table (\n$cols\n)";
		}
	}
	
	
	/**
	* 
	* Returns the SQL statement to create an index.
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
	* Indexes are automatically renamed to "tablename__indexname__idx".
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
		
		// we prefix all index names with the table name,
		// and suffix all index names with '__idx'.  this
		// is to soothe PostgreSQL, which demands that index
		// names not collide, even when they indexes are on
		// different tables.
		$fullname = $table . '__' . $name . '__idx';
		
		/*
		// REMOVED because we have double-unders in the final name.
		// shouldn't matter, no reserved words in __idx anyway.
		// full index name cannot be a reserved keyword
		if (! $this->validWord($fullname)) {
			return $this->error(
				'ERR_IDX_WORD',
				array('table' => $table, 'name' => $name),
				E_USER_WARNING
			);
		}
		*/
		
		// build up the index information for type and columns
		$type = null;
		$cols = null;
		if (is_string($info)) {
			// shorthand for index names: colname => index_type
			$type = trim($info);
			$cols = trim($name);
		} elseif (is_array($info)) {
			// normal: index_name => array('type' => ..., 'cols' => ...)
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
	
	public function validWord($word)
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