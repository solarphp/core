<?php

/**
* 
* Class for connecting to SQL databases and performing common operations.
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
* The base class for SQL drivers.
*/

require_once 'Solar/Sql/Driver.php';

/**
* The class for reporting SELECT results.
*/

require_once 'Solar/Sql/Result.php';


/**
* 
* Class for connecting to SQL databases and performing common operations.
* 
* Example usage:
* 
* <code>
* 
* $opts = array(
* 	'driver' => 'Solar_Sql_Driver_Mysql',
* 	'host'   => 'localhost',
* 	'user'   => 'pmjones',
* 	'pass'   => '********',
* 	'name'   => 'test'
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
* @package Solar_Sql
* 
*/


class Solar_Sql extends Solar_Base {
	
	
	/**
	* 
	* User-provided configuration.
	* 
	* Keys are:
	* 
	* class => (string) Driver class, e.g. 'Solar_Sql_Driver_Mysql'.
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
		'class' => null,
		'host'  => null,
		'port'  => null,
		'user'  => null,
		'pass'  => null,
		'name'  => null,
		'mode'  => null
	);
	
	
	/**
	* 
	* The database backend driver object.
	* 
	* For now, we use sub-drivers for MySQL, SQLite, PostgreSQL, et. al.
	* However, when PDO comes online, this will become a PDO object.
	* 
	* @access protected
	* 
	* @var object
	*
	*/
	
	protected $driver = null;
	
	
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
		
		// get the driver class
		$class = $this->config['class'];
		
		// set up the driver config array
		$opts = $this->config;
		
		// don't override these in the driver
		unset($opts['class']);
		
		// create the driver object, and we're done.
		$this->driver = Solar::object($class, $opts);
	}
	
	
	/**
	* 
	* Executes an SQL statement (with optional bound data).
	* 
	* @access public
	* 
	* @param string $stmt The text of the SQL statement, with
	* placeholders.
	* 
	* @param array $data An associative array of data to use for the
	* placeholders.
	* 
	* @param int $count If you want to limit the returned results (as in
	* a SELECT statement) this is the number of rows to return.
	* 
	* @param int $offset If you want to limit the returned results (as
	* in a SELECT statement) this is the row number to start at.
	* 
	* @return mixed Solar_Sql_Result in the case of SELECT statements, a
	* Solar_Error on error, or boolean true if a non-SELECT statement
	* succeeded.
	* 
	*/
	
	public function exec($stmt, $data = null, $count = 0, $offset = 0)
	{
		// if binding data is available, bind it into the statement.
		if (is_array($data)) {
			$stmt = $this->bind($stmt, $data);
		}
		
		// if it's a SELECT statement, and a count is specified,
		// modify the statement so it has a limit-count.
		if (strtoupper(substr($stmt, 0, 7)) == 'SELECT ' &&
			($count > 0 || $offset > 0)) {
			// modify in-place to include a limiting count
			$this->driver->limit($stmt, (int) $count, (int) $offset);
		}
		
		// return the results of the statement execution.
		return $this->driver->exec($stmt, $count, $offset);
	}
	
	
	/**
	* 
	* Binds placeholder text in an SQL statement with quoted values.
	* 
	* E.g., if $text = "SELECT * FROM table WHERE user = :user" and your
	* $data = array('user' => 'bolivar') then the bound result would be
	* "SELECT * FROM table WHERE user = 'bolivar'".
	* 
	* @access public
	* 
	* @param string $stmt The text of the SQL statement, with
	* placeholders.
	* 
	* @param array $data An associative array of data to use for the
	* placeholders.
	* 
	* @return string The statement with quoted values bound in place.
	*/
	
	public function bind($stmt, $data)
	{
		$regex = '/(:[A-Za-z0-9_]+)/';
		$split = preg_split($regex, $stmt, -1, PREG_SPLIT_DELIM_CAPTURE);
		$stmt = '';
		foreach ($split as $part) {
			if (substr($part, 0, 1) == ':') {
				$key = substr($part, 1);
				$stmt .= $this->quote($data[$key]);
			} else {
				$stmt .= $part;
			}
		}
		return $stmt;
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
		
		// add field names
		$stmt .= '(' . implode(', ', array_keys($data)) . ') ';
		
		// add value placeholders
		$stmt .= 'VALUES (:' . implode(', :', array_keys($data)) . ')';
		
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
		
		// add the where clause, execte, and return
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
		$stmt = "DELETE FROM $table WHERE $where";
		return $this->exec($stmt);
	}
	
	
	/**
	* 
	* Convenience method to fetch all returned rows.
	* 
	* @access public
	* 
	* @param string $stmt The text of the SQL statement, with placeholders.
	* 
	* @param array $data An associative array of data to use for the
	* placeholders.
	* 
	* @param int $count If you want to limit the returned results (as in 
	* a SELECT statement) this is the number of rows to return.
	* 
	* @param int $offset If you want to limit the returned results (as in 
	* a SELECT statement) this is the row number to start at.
	* 
	* @return array An array of all returned rows.
	* 
	*/
	
	public function fetchAll($stmt, $data = null, $count = null,
		$offset = null)
	{
		// get the result set
		$result = $this->exec($stmt, $data, $count, $offset);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// capture all cols of all rows.
		$data = array();
		while ($row = $result->fetch()) {
			$data[] = $row;
		}
		
		// free the result set and return
		unset($result);
		return $data;
	}
	
	
	/**
	* 
	* Convenience method to get the query results as an associative array.
	* 
	* @access public
	* 
	* @param string $stmt The text of the SQL statement, with placeholders.
	* 
	* @param array $data An associative array of data to use for the
	* placeholders.
	* 
	* @param int $count If you want to limit the returned results (as in 
	* a SELECT statement) this is the number of rows to return.
	* 
	* @param int $offset If you want to limit the returned results (as in 
	* a SELECT statement) this is the row number to start at.
	* 
	* @return array An associative array where the key is the first column 
	* in the row and the value is an associative array of all remaining 
	* columns.
	* 
	*/
	
	public function fetchAssoc($stmt, $data = null, $count = null,
		$offset = null)
	{
		// get the result set
		$result = $this->exec($stmt, $data, $count, $offset);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// capture data as key-value pairs where the first column
		// is the key and the second column is the value
		$data = array();
		while ($row = $result->fetch()) {
			$key = array_shift($row);
			$data[$key] = $row;
		}
		
		// free the result set and return
		unset($result);
		return $data;
	}
	
	
	/**
	* 
	* Convenience method to fetch the entire first column of values.
	* 
	* @access public
	* 
	* @param string $stmt The text of the SQL statement, with placeholders.
	* 
	* @param array $data An associative array of data to use for the
	* placeholders.
	* 
	* @param int $count If you want to limit the returned results (as in 
	* a SELECT statement) this is the number of rows to return.
	* 
	* @param int $offset If you want to limit the returned results (as in 
	* a SELECT statement) this is the row number to start at.
	* 
	* @return array The first value in each row of the query result.
	* 
	*/
	
	public function fetchCol($stmt, $data = null, $count = null,
		$offset = null)
	{
		// get the result set
		$result = $this->exec($stmt, $data, $count, $offset);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// capture the first col of every row.
		$data = array();
		while ($row = $result->fetchNum()) {
			$data[] = $row[0];
		}
		
		// free the result set and return
		unset($result);
		return $data;
	}
	
	
	/**
	* 
	* Convenience method to fetch only the first value from a query.
	* 
	* @access public
	* 
	* @param string $stmt The text of the SQL statement, with placeholders.
	* 
	* @param array $data An associative array of data to use for the
	* placeholders.
	* 
	* @return string The first value in the first row of the query result.
	* 
	*/
	
	public function fetchOne($stmt, $data = null)
	{
		// get the result set (always and only with a count of 1)
		$result = $this->exec($stmt, $data, 1);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// capture the first row
		$row = $result->fetchNum();
		
		// free the result set and return the first value of the row
		unset($result);
		return $row[0];
	}
	
	
	/**
	* 
	* Convenience method to get the query results as a key-value pairs.
	* 
	* @access public
	* 
	* @param string $stmt The text of the SQL statement, with placeholders.
	* 
	* @param array $data An associative array of data to use for the
	* placeholders.
	* 
	* @param int $count If you want to limit the returned results (as in 
	* a SELECT statement) this is the number of rows to return.
	* 
	* @param int $offset If you want to limit the returned results (as in 
	* a SELECT statement) this is the row number to start at.
	* 
	* @return array An associative array where the key is the first value 
	* in the row and the value is the second value in the row.
	* 
	*/
	
	public function fetchPair($stmt, $data = null, $count = null,
		$offset = null)
	{
		// get the result set
		$result = $this->exec($stmt, $data, $count, $offset);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// capture data as key-value pairs where the first column
		// is the key and the second column is the value
		$data = array();
		while ($row = $result->fetchNum()) {
			$data[$row[0]] = $row[1];
		}
		
		// free the result set and return
		unset($result);
		return $data;
	}
	
	
	/**
	* 
	* Convenience method to fetch the first row of values from a query.
	* 
	* @access public
	* 
	* @param string $stmt The text of the SQL statement, with placeholders.
	* 
	* @param array $data An associative array of data to use for the
	* placeholders.
	* 
	* @return array The first row of the query.
	* 
	*/
	
	public function fetchRow($stmt, $data = null)
	{
		// get the result set (always and only with a count of 1)
		$result = $this->exec($stmt, $data, 1);
		if (Solar::isError($result)) {
			return $result;
		}
		
		// capture the first row.
		$data = $result->fetch();
		
		// free the result set and return
		unset($result);
		return $data;
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
		return $this->fetchCol($this->driver->listTables());
	}
	
	
	/**
	* 
	* Safely enquotes a value for an SQL statement.
	* 
	* @access public
	* 
	* @param mixed $val
	* 
	* @return string An SQL-safe quoted string.
	* 
	*/
	
	public function quote($val)
	{
		if (is_int($val) || is_double($val)) {
			// it's a number
			settype($val, 'string');
		} elseif (is_bool($val)) {
			// it's boolean
			$val = $val ? '1' : '0';
		} elseif (is_null($val)) {
			// it's null
			$val = 'NULL';
		} else {
			// all others are treated as strings.
			// escape the value, and enquote it.
			$val = "'" . $this->driver->escape($val) . "'";
		}
		return $val;
	}
	
	
	/**
	* 
	* Creates a table and its columns in the database.
	* 
	* @access public
	* 
	* @param string $table The table name.
	* 
	* @param array $cols An associative array of column information
	* where the key is the column name and the value is an associative
	* array of information about the column.
	* 
	* @return mixed
	* 
	*/
	
	public function createTable($table, $cols)
	{
		$stmt = $this->driver->createTable($table, $cols);
		if (Solar::isError($stmt)) {
			return $stmt;
		} else {
			return $this->driver->exec($stmt);
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
		return $this->driver->exec("DROP TABLE $table");
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
		$declare = $this->driver->columnString($name, $info);
		if (Solar::isError($declare)) {
			return $declare;
		} else {
			$stmt = "ALTER TABLE $table ADD COLUMN $declare";
			return $this->driver->exec($stmt);
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
		$stmt = "ALTER TABLE $table DROP COLUMN $name";
		return $this->driver->exec($stmt);
	}
	
	
	/**
	* 
	* Creates an index on a table in the database.
	* 
	* @access public
	* 
	* @param string $table The table name.
	* 
	* @param string $name The index name to create.
	* 
	* @param array $info Information about the index.
	* 
	* @return mixed
	* 
	*/
	
	public function createIndex($table, $name, $info)
	{
		$stmt = $this->driver->createIndex($table, $name, $info);
		if (Solar::isError($stmt)) {
			return $stmt;
		} else {
			return $this->driver->exec($stmt);
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
		$stmt = "DROP INDEX $name ON $table";
		return $this->driver->exec($stmt);
	}
	
	
	/**
	* 
	* Create a sequence in the database.
	* 
	* @access public
	* 
	* @param string $name The index name to drop.
	* 
	* @param string $start The starting sequence number.
	* 
	* @return mixed
	* 
	*/
	
	public function createSequence($name, $start = 1)
	{
		$name .= '__seq';
		return $this->driver->createSequence($name, $start);
	}
	
	
	/**
	* 
	* Drop a sequence from the database.
	* 
	* @access public
	* 
	* @param string $name The index name to drop.
	* 
	* @param string $start The starting sequence number.
	* 
	* @return mixed
	* 
	*/
	
	public function dropSequence($name)
	{
		$name .= '__seq';
		return $this->driver->dropSequence($name);
	}
	
	
	/**
	* 
	* Gets a sequence number; creates the sequence if it does not exist.
	* 
	* Technically, you only need one sequence for your entire database;
	* the number itself is not important, only that it is unique.
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
		$name .= '__seq';
		return $this->driver->nextSequence($name);
	}
}
?>