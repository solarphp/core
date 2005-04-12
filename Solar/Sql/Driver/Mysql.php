<?php

/**
* 
* Class for connecting to MySQL databases.
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
* Class for connecting to MySQL databases.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Sql
* 
*/

class Solar_Sql_Driver_Mysql extends Solar_Sql_Driver {
	
	
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
        'bool'      => 'DECIMAL(1,0)',
        'char'      => 'CHAR(:size) BINARY',
        'varchar'   => 'VARCHAR(:size) BINARY',
        'smallint'  => 'SMALLINT',
        'int'       => 'INTEGER',
        'bigint'    => 'BIGINT',
        'numeric'   => 'DECIMAL(:size,:scope)',
        'float'     => 'DOUBLE',
        'clob'      => 'LONGTEXT',
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
		parent::__construct($config);
		
		// try to connect.
		$this->conn = @mysql_connect(
			$this->config['host'],
			$this->config['user'],
			$this->config['pass']
		);
		
		// did it work?
		if (! $this->conn) {
			// no, return an error.
			$this->error(
				'ERR_CONNECT',
				array('host' => $this->config['host'], 'user' => $this->config['user']),
				E_USER_ERROR
			);
		} else {
			// turn on autocommit
			@mysql_query('SET AUTOCOMMIT=1', $this->conn);
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
		return @mysql_real_escape_string($val);
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
		$result = @mysql_select_db($this->config['name'], $this->conn);
		if (! $result) {
			return $this->error(
				'ERR_DATABASE',
				array('host' => $this->config['host'], 'name' => $this->config['name']),
				E_USER_ERROR
			);
		}
		
		// now attempt the query
		$result = @mysql_query($stmt, $this->conn);
		
		// process the result
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
					'code' => @mysql_errno(),
					'text' => @mysql_error()
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
		return @mysql_fetch_assoc($rsrc);
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
		return @mysql_fetch_row($rsrc);
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
		return @mysql_free_result($rsrc);
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
		return "SHOW TABLES";
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
		$result = $this->exec("CREATE TABLE $name (id INT NOT NULL)");
		$result = $this->exec("INSERT INTO $name (id) VALUES ($start)");
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
		$result = $this->exec("UPDATE $name SET id = LAST_INSERT_ID(id+1)");
		
		// did it work?
		if (! $result || Solar::isError($result)) {
			// error when updating the sequence.
			// assume we need to create it.
			$this->createSequence($name);
			
			// now try the sequence number again.
			$this->exec("UPDATE $name SET id = LAST_INSERT_ID(id+1)");
		}
		
		// get the sequence number
		$result = $this->exec("SELECT LAST_INSERT_ID()");
		$row = $result->fetchNum();
		return $row[0];
	}
}
?>
