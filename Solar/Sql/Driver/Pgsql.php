<?php

/**
* 
* Class for connecting to PostgreSQL databases.
* 
* @category Solar
* 
* @package Solar_Sql
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id: Pgsql.php,v 1.16 2005/02/08 01:42:26 pmjones Exp $
* 
*/

/**
* 
* Class for connecting to PostgreSQL databases.
* 
* @category Solar
* 
* @package Solar_Sql
* 
*/

class Solar_Sql_Driver_Pgsql extends Solar_Sql_Driver {
	
	
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
        'float'     => 'DOUBLE PRECISION',
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
		
		/** @todo Add the port specification */
		// try to connect
		$this->conn = @pg_connect("host=$this->config['host'] dbname=$this->config['name'] " .
			"user=$this->config['user'] pass=$this->config['pass']");
		
		// did it work?
		if (! $this->conn) {
			$this->error(
				'ERR_CONNECT',
				array(
					'name' => $this->config['name'],
					'host' => $this->config['host'],
					'user' => $this->config['user']
				),
				E_USER_ERROR
			);
		} else {
			// turn on autocommit explicitly
			@pg_query($this->conn, 'SET AUTOCOMMIT TO ON');
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
		return @pg_escape_string($val);
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
		$result = @pg_query($this->conn, $stmt);
		
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
			return $this->error(
				'ERR_STATEMENT',
				array(
					'text' => @pg_last_error()
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
		return @pg_fetch_assoc($rsrc);
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
		return @pg_fetch_row($rsrc);
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
		return @pg_free_result($rsrc);
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
		return "SELECT c.relname AS table_name " .
			"FROM pg_class c, pg_user u " .
			"WHERE c.relowner = u.usesysid AND c.relkind = 'r' " .
			"AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) " .
			"AND c.relname !~ '^(pg_|sql_)' " .
			"UNION " .
			"SELECT c.relname AS table_name " .
			"FROM pg_class c " .
			"WHERE c.relkind = 'r' " .
			"AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) " .
			"AND NOT EXISTS (SELECT 1 FROM pg_user WHERE usesysid = c.relowner) " .
			"AND c.relname !~ '^pg_'";
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
		$this->exec("CREATE SEQUENCE $name START $start");
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
		$result = $this->exec("SELECT NEXTVAL($name)");
		
		// did it work?
		if (! $result || Solar::isError($result)) {
			// error when updating the sequence.
			// assume we need to create it.
			$this->createSequence($name);
			
			// now try the sequence number again.
			$result = $this->exec("SELECT NEXTVAL($name)");
		}
		
		// get the sequence number
		$row = $result->fetchNum();
		return $row[0];
	}
}
?>