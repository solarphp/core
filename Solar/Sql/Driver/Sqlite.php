<?php

/**
* 
* Class for connecting to SQLite databases.
* 
* @category Solar
* 
* @package Solar_Sql
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id: Sqlite.php,v 1.17 2005/02/08 01:42:26 pmjones Exp $
* 
*/

/**
* 
* Class for connecting to SQLite databases.
* 
* @category Solar
* 
* @package Solar_Sql
* 
*/

class Solar_Sql_Driver_Sqlite extends Solar_Sql_Driver {
	
	
	/**
	* 
	* Map of Solar generic columnt types to RDBMS native declarations.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $native = array(
        'bool'      => 'BOOLEAN',
        'char'      => 'CHAR',
        'varchar'   => 'VARCHAR',
        'smallint'  => 'SMALLINT',
        'int'       => 'INTEGER',
        'bigint'    => 'BIGINT',
        'numeric'   => 'NUMERIC',
        'float'     => 'DOUBLE',
        'clob'      => 'CLOB',
        'date'      => 'DATE',
        'time'      => 'TIME',
        'timestamp' => 'TIMESTAMP'
	);
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	*/
	
	public function __construct($conf = null)
	{
		// basic construction
		parent::__construct($conf);
		
		// try to connect
		$this->conn = @sqlite_open($this->config['name'], $this->config['mode']);
		
		// did it work?
		if (! $this->conn) {
			$this->error(
				'ERR_CONNECT',
				array('name' => $this->config['name']),
				E_USER_ERROR
			);
		}
	}
	
	
	/**
	* 
	* Provides the proper escaping for enquoted values.
	* 
	* The code presented here works for Fbsql, Mssql, and Oci.
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
		return @sqlite_escape_string($val);
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
		// no need to re-select a database, that's part
		// of the connection parameters.
		//
		// attempt the query.
		$result = @sqlite_query($stmt, $this->conn);
		
		// what was the result?
		if (is_resource($result)) {
		
			// fetchable success
			$opts = array(
				'driver' => $this,
				'rsrc'   => $result
			);
			return new Solar_Sql_Result($opts);
			
		} elseif (! $result) {
		
			// failure
			$err = @sqlite_last_error();
			return $this->error(
				'ERR_STATEMENT',
				array(
					'code' => $err,
					'text' => @sqlite_error_string($err),
				),
				E_USER_WARNING
			);
			
		} else {
		
			// generic success
			return $result;
			
		}
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
	
	public function fetch(&$rsrc)
	{
		return @sqlite_fetch_array($rsrc, SQLITE_ASSOC);
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
	
	public function fetchNum(&$rsrc)
	{
		return @sqlite_fetch_array($rsrc, SQLITE_NUM);
	}
	
	
	/**
	* 
	* Adds a LIMIT clause (or equivalent) to an SQL statement in-place.
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
		if ($count > 0) {
			$stmt .= " LIMIT $count";
			if ($offset > 0) {
				$stmt .= " OFFSET $offset";
			}
		}
	}
	
	
	/**
	* 
	* Returns the SQL statement to get a list of database tables.
	* 
	* @access public
	* 
	* @return string The SQL statement.
	* 
	*/
	
	public function listTables()
	{
		// copied from PEAR DB
		return "SELECT name FROM sqlite_master WHERE type='table' " .
			"UNION ALL SELECT name FROM sqlite_temp_master " .
			"WHERE type='table' ORDER BY name";
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
		$start -= 1;
		$this->exec("CREATE TABLE $name (id INTEGER PRIMARY KEY)");
		$this->exec("INSERT INTO $name (id) VALUES ($start)");
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
		$this->exec("DROP TABLE $name");
	}
	
	
	/**
	* 
	* Gets a sequence number; creates the sequence if it does not exist.
	* 
	* @access public
	* 
	* @param string $name The sequence name.
	* 
	* @return int The next sequence number.
	* 
	*/
	
	public function nextSequence($name)
	{
		// first, try to get the next sequence number, assuming
		// the table exists.
		$result = $this->exec("INSERT INTO $name (id) VALUES (NULL)");
		
		// did it work?
		if (Solar::isError($result)) {
		
			// error when updating the sequence.
			// assume we need to create it.
			$this->createSequence($name);
			
			// now try the sequence number again.
			$result = $this->exec("INSERT INTO $name (id) VALUES (NULL)");
			
		}
		
		// now that we have a new sequence number, delete any earlier rows
		// to keep the table small.  should this be a trigger instead?
		$this->exec("DELETE FROM $name WHERE id < LAST_INSERT_ROWID()");
		
		// return the sequence number
		return @sqlite_last_insert_rowid($this->conn);
	}
}
?>