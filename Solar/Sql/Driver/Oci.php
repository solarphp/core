<?php

/**
* 
* Class for connecting to Oracle8 and later SQL databases.
* 
* @category Solar
* 
* @package Solar_Sql
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id: Oci.php,v 1.17 2005/02/08 01:42:26 pmjones Exp $
* 
*/

/**
* 
* Class for connecting to Oracle8 and later SQL databases.
* 
* @category Solar
* 
* @package Solar_Sql
* 
*/

class Solar_Sql_Driver_Oci extends Solar_Sql_Driver {
	
	
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
        'bool'      => 'NUMBER(1)',
        'char'      => 'CHAR',
        'varchar'   => 'VARCHAR2',
        'smallint'  => 'NUMBER(6)',
        'int'       => 'NUMBER(11)',
        'bigint'    => 'NUMBER(19)',
        'numeric'   => 'NUMBER',
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
	
	public function __construct($conf = null)
	{
		
		// basic construction
		parent::__construct($conf);
		
		// try to connect.  we use "new" connection to make sure
		// transactions don't step on each other (PHP commits transactions
		// by connection, not by SQL command).
		$this->conn = @oci_new_connect($this->config['user'], $this->config['pass'], $this->config['name']);
		
		// did it work?
		if (! $this->conn) {
			$this->error(
				'ERR_CONNECT',
				@oci_error()
				E_USER_ERROR
			);
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
		// no need to re-select a database, that's part
		// of the connection parameters.
		//
		// prepare the statement and attempt the query.
		$prepStmt = @oci_parse($this->conn, $stmt);
		$result = @oci_execute($prepStmt, OCI_COMMIT);
		
		// with Oci, the returned result is only boolean, never a
		// resource.  you use the prepared statement to fetch rows with,
		// not the query return value.  this means that we have to check
		// the statement type to see if it's a fetch-able kind of
		// return.
		if (! $result) {
			
			// failure
			return $this->error(
				'ERR_STATEMENT',
				@oci_error($this->conn),
				E_USER_WARNING
			);
			
		} elseif (@oci_statement_type() == 'SELECT') {
			
			// fetchable success.  note that the prepared statement
			// is used as the resource, not the query result.
			$opts = array(
				'driver' => $this,
				'rsrc'   => $prepStmt
			);
			
			return new Solar_Sql_Result($opts);
			
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
		return @oci_fetch_assoc($rsrc);
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
		return @oci_fetch_row($rsrc);
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
	
	public function free(&$rsrc)
	{
		return @oci_free_statement($rsrc);
	}
	
	
	/**
	* 
	* Adds a LIMIT clause (or equivalent) to an SQL statement.
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
			if ($offset > 0) {
				$count += $offset;
				$stmt = "SELECT * FROM ($stmt) WHERE ROWNUM >= $offset" .
					" AND ROWNUM <= $count";
			} else {
				$stmt = "SELECT * FROM ($stmt) WHERE ROWNUM <= $count";
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
		return 'SELECT table_name FROM user_tables';
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
		$this->exec("CREATE SEQUENCE $name START WITH $start");
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
		$this->exec("DROP SEQUENCE $name");
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
		// the sequence exists.
		$result = $this->exec("SELECT ({$name}.nextval) FROM DUAL");
		
		// did it work?
		if (! $result || Solar::isError($result)) {
			// error when updating the sequence.
			// assume we need to create it.
			$this->createSequence($name);
			
			// now try the sequence number again.
			$result = $this->exec("SELECT ({$name}.nextval) FROM DUAL");
		}
		
		// get the sequence number
		$row = $result->fetchNum();
		return $row[0];
	}
}
?>