<?php

/**
* 
* Class for connecting to Microsoft SQL databases.
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
* Class for connecting to Microsoft SQL databases.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Sql
* 
*/

class Solar_Sql_Driver_Mssql extends Solar_Sql_Driver {
	
	
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
        'bool'      => 'BIT',
        'char'      => 'BINARY(:size)',
        'varchar'   => 'VARBINARY(:size)',
        'smallint'  => 'SMALLINT',
        'int'       => 'INTEGER',
        'bigint'    => 'BIGINT',
        'numeric'   => 'DECIMAL(:size,:scope)',
        'float'     => 'FLOAT',
        'clob'      => 'TEXT',
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
		$this->conn = @mssql_connect($this->config['host'], $this->config['user'], $this->config['pass']);
		
		// did it work?
		if (! $this->conn) {
			$this->error(
				'ERR_CONNECT',
				array(
					'host' => $this->config['host'],
					'user' => $this->config['user']
				),
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
	* The emulated select offset in this method is terribly inefficient.
	* 
	* Emulates auto-commit behavior.
	* 
	* Possible solution:
	* http://richardathome.no-ip.com/index.php?article_id=412
	* 
	* DECLARE @PAGESIZE INT
	* DECLARE @CURRENT_PAGE INT
	* 
	* SET @PAGESIZE = 5
	* SET @CURRENT_PAGE = 2
	* 
	* SET ROWCOUNT @PAGESIZE
	* 
	* SELECT AU_ID, AU_LNAME, AU_FNAME, PHONE,
	* (SELECT COUNT(*) FROM AUTHORS A2 WHERE A2.AU_LNAME <= A.AU_LNAME
	* AND AU_FNAME LIKE '%A%') AS RowNumber
	* FROM AUTHORS A
	* WHERE AU_FNAME LIKE '%A%' AND (SELECT COUNT(*) FROM AUTHORS A2
	* WHERE A2.AU_LNAME <= A.AU_LNAME AND AU_FNAME LIKE '%A%') >
	* (@PAGESIZE * @CURRENT_PAGE) - @PAGESIZE
	* ORDER BY AU_LNAME
	* 
	* SET ROWCOUNT 0
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
		$result = @mssql_select_db($this->config['name'], $this->conn);
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
		
		// now attempt the query, emulate autocommit.
		@mssql_query('BEGIN TRAN');
		$result = @mssql_query($stmt, $this->conn);
		
		// what happened?
		if (is_resource($result)) {
			
			// fetchable success. auto-commit.
			@mssql_query('COMMIT TRAN');
			
			// mssql does not have native support for offsets.
			// this is ugly and ineffecient for large result sets,
			// but it works.
			if (strtoupper(substr($stmt, 0, 7)) == 'SELECT ' &&
				$offset > 0) {
				mssql_data_seek($result, $offset);
			}
			
			// build the result object and return it.
			$opts = array(
				'class'  => __CLASS__,
				'rsrc'   => $result
			);
			return new Solar_Sql_Result($opts);
			
		} elseif (! $result) {
			
			// failure, roll back from the emulated auto-commit.
			@mssql_query('ROLLBACK TRAN');
			
			// report error
			return $this->error(
				'ERR_STATEMENT',
				array(
					'text' => @mssql_get_last_message()
				),
				E_USER_WARNING
			);
			
		} else {
		
			// generic success. auto-commit.
			@mssql_query('COMMIT TRAN');
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
		return @mssql_fetch_assoc($rsrc);
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
		return @mssql_fetch_row($rsrc);
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
		return @mssql_free_result($rsrc);
	}
	
	
	/**
	* 
	* Adds a LIMIT clause (or equivalent) to an SQL statement.
	* 
	* This method only adds a TOP $count clause; the exec() method
	* skips ahead to the $offset value.  Terribly inefficient.
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
			// emulating offsets for MS-SQL (this sucks)
			if ($offset > 0) {
				$count += $offset;
			}
			
			$tmp = substr($stmt, 0, 7);
			$stmt = $tmp . "TOP $count " . substr($stmt, 8);
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
		 return "SELECT name FROM sysobjects WHERE type = 'U' ORDER BY name";
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
		$this->exec(
			"CREATE TABLE $name (id INT NOT NULL " .
			'IDENTITY($start,1) PRIMARY KEY CLUSTERED)'
		);
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
		$result = $this->exec("INSERT INTO $name DEFAULT VALUES");
		
		// did it work?
		if (! $result || Solar::isError($result)) {
			// error when updating the sequence.
			// assume we need to create it.
			$this->createSequence($name);
			
			// now try the sequence number again.
			$this->exec("INSERT INTO $name DEFAULT VALUES");
		}
		
		// get the sequence number
		$result = $this->exec("SELECT @@IDENTITY FROM $name");
		$row = $result->fetchNum();
		$id = $row[0];
		
		// now that we have a new sequence number, delete any earlier rows
		// to keep the table small.  should this be a trigger instead?
		$this->exec("DELETE FROM $name WHERE id < $id");
		
		// return the sequence number
		return $id;
	}
}
?>
