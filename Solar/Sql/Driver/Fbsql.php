<?php

/**
* 
* Class for connecting to Frontbase SQL databases.
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
* Class for connecting to Frontbase SQL databases.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Sql
* 
*/

class Solar_Sql_Driver_Fbsql extends Solar_Sql_Driver {
	
	
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
        'bool'      => 'DECIMAL(1,0)',
        'char'      => 'CHAR',
        'varchar'   => 'VARCHAR',
        'smallint'  => 'SMALLINT',
        'int'       => 'INTEGER',
        'bigint'    => 'LONGINT',
        'numeric'   => 'DECIMAL',
        'float'     => 'DOUBLE PRECISION',
        'clob'      => 'CLOB',
        'date'      => 'CHAR(10)',
        'time'      => 'CHAR(8)',
        'timestamp' => 'CHAR(19)'
	);
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	*/
	
	public function __construct($config = null)
	{
		// basic construction
		parent::__construct($config);
		
		// try to connect
		$this->conn = @fbsql_connect($this->config['host'], $this->config['user'], $this->config['pass']);
		
		// did it work?
		if (! $this->conn) {
			// no, return an error
			$this->error(
				'ERR_CONNECT',
				array(
					'host' => $this->config['host'],
					'user' => $this->config['user']
				),
				E_USER_ERROR
			);
		} else {
			// yes, turn on auto-commits
			@fbsql_query('SET COMMIT TRUE', $this->conn);
		}
	}
	
	
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
		return str_replace("'", "''", $val);
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
		// always re-select the database; we may be re-using
		// this connection for multiple databases. this is not
		// a problem with some other drivers, as they select
		// the database as part of the initial connection.
		$result = @fbsql_select_db($this->config['name'], $this->conn);
		if (! $result) {
			return $this->error(
				'ERR_DATABASE',
				array(
					'host' => $this->config['host'],
					'name' => $this->config['name']
				),
				E_USER_ERROR
			);
		}
		
		// now attempt the query
		$result = @fbsql_query($stmt, $this->conn);
		
		// what happened?
		if (is_resource($result)) {
		
			// fetchable success
			$opts = array(
				'class'  => __CLASS__,
				'rsrc'   => $result
			);
			return new Solar_Sql_Result($opts);
			
		} elseif (! $result) {
		
			// failure
			return $this->error(
				'ERR_STATEMENT',
				array(
					'code' => @fbsql_errno(),
					'text' => @fbsql_error()
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
	
	public static function fetch(&$rsrc)
	{
		return @fbsql_fetch_assoc($rsrc);
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
		return @fbsql_fetch_row($rsrc);
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
		return @fbsql_free_result($rsrc);
	}
	
	
	/**
	* 
	* Adds a LIMIT clause (or equivalent) to an SQL statement.
	* 
	* This method is dumb; it adds the clause to any kind of statement.
	* You should not call it directly; use exec() with the optional $count
	* and $offset parameters instead.
	* 
	* @access protected
	* 
	* @param string &$stmt The SQL statement to modify.
	* 
	* @param int $count The number of records to SELECT.
	* 
	* @param int $offset The number of records to skip on SELECT.
	* 
	* @return string The modified SQL query.
	* 
	*/
	
	public function limit(&$stmt, $count = 0, $offset = 0)
	{
		if ($count > 0) {
			// get the 'SELECT ' opening word and space
			$tmp = substr($stmt, 0, 7);
			
			// are we adding an offset as well?
			if ($offset > 0) {
				// yes
				$stmt = $tmp . "TOP($offset,$count) " . substr($stmt, 8);
			} else {
				// no, just a top
				$stmt = $tmp . "TOP(0,$count) " . substr($stmt, 8);
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
		return 'SELECT "table_name" FROM information_schema.tables';
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
		$this->exec(
			"CREATE TABLE $name (" .
			'id INTEGER UNSIGNED AUTO_INCREMENT NOT NULL,' .
			' PRIMARY KEY(id))'
		);
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
	
	public function nextSequence($name = 'hive')
	{
		// first, try to get the next sequence number, assuming
		// the table exists.
		$result = $this->exec("INSERT INTO $name (id) VALUES (NULL)");
		
		// did it work?
		if (! $result || Solar::isError($result)) {
			// error when updating the sequence.
			// assume we need to create it.
			$this->createSequence($name);
			
			// now try the sequence number again.
			$this->exec("INSERT INTO $name (id) VALUES (NULL)");
		}
		
		// get the sequence number
		$id = @fbsql_insert_id($this->connection);
		
		// now that we have a new sequence number, delete any earlier rows
		// to keep the table small.
		$this->exec("DELETE FROM $name WHERE id < $id");
		
		// return the sequence number
		return $id;
	}
}
?>
