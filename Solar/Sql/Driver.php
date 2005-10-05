<?php

/**
* 
* Base class for specific RDBMS driver information.
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
* Base class for specific RDBMS driver information.
* 
* @category Solar
* 
* @package Solar
* 
* @subpackage Solar_Sql
* 
*/

class Solar_Sql_Driver extends Solar_Base {
	
	
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
	
	protected $config = array(
		'locale' => 'Solar/Sql/Locale/',
		'host'   => null,
		'port'   => null,
		'user'   => null,
		'pass'   => null,
		'name'   => null,
		'mode'   => null,
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
	
	public $pdo = null;
	
	
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
		'bool'      => null,
		'char'      => null, 
		'varchar'   => null, 
		'smallint'  => null,
		'int'       => null,
		'bigint'    => null,
		'numeric'   => null,
		'float'     => null,
		'clob'      => null,
		'date'      => null,
		'time'      => null,
		'timestamp' => null
	);
	
	
	/**
	* 
	* The PDO driver DSN type.
	* 
	* This might not be the same as the Solar driver type.
	* 
	* @access protected
	* 
	* @var string
	* 
	*/
	
	protected $pdo_type = null;
	
	
	/**
	* 
	* Creates a PDO-style DSN.
	* 
	* @access public
	* 
	* @return string A PDO-style DSN.
	* 
	*/
	
	public function dsn()
	{
		$tmp = array();
		
		if ($this->config['host']) {
			$tmp[] = 'host=' . $this->config['host'];
		}
		
		if ($this->config['name']) {
			$tmp[] = 'dbname=' . $this->config['name'];
		}
		
		return $this->pdo_type . ':' . implode(';', $tmp);
	}
	
	
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
		$dsn = $this->dsn();
		
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
			$err = $this->errorException($e, E_USER_ERROR);
			return $err;
		}
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
			$err = $this->errorException($e, E_USER_WARNING);
			return $err;
		}
		
		// execute with bound data
		try {
			$obj->execute($data);
		} catch (Exception $e) {
			$err = $this->errorException($e, E_USER_WARNING);
			return $err;
		}
		
		// return the results embedded in the prepared statement object
		return $obj;
	}
	
	
	/**
	* 
	* Safely quotes a value for an SQL statement.
	* 
	* Quotes individual array values (but not their keys).
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
			// quote array values, not keys, and only one level's worth
			// (i.e., non-recursive).
			foreach ($val as $k => $v) {
				$val[$k] = $this->pdo->quote($v);
			}
		} else {
			$val = $this->pdo->quote($val);
		}
		return $val;
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
	* Returns a list of database tables.
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
	* Returns a list of native column types.
	* 
	* @access public
	* 
	* @return array
	* 
	*/
	
	public function nativeColTypes()
	{
		return $this->native;
	}
	
	
	/**
	* 
	* Build an SQL SELECT statement from its component parts.
	* 
	* Note that this base method does not add LIMITs.  Your extended
	* driver class should do so.
	* 
	* @access protected
	* 
	* @return string An SQL SELECT statement.
	* 
	*/
	
	public function buildSelect($parts)
	{
		// is this a SELECT or SELECT DISTINCT?
		if ($parts['distinct']) {
			$stmt = "SELECT DISTINCT\n\t";
		} else {
			$stmt = "SELECT\n\t";
		}
		
		// add columns
		$stmt .= implode(",\n\t", $parts['cols']) . "\n";
		
		// from these tables
		$stmt .= "FROM ";
		$stmt .= implode(", ", $parts['from']) . "\n";
		
		// joined to these tables
		if ($parts['join']) {
			$list = array();
			foreach ($parts['join'] as $join) {
				$tmp = '';
				// add the type (LEFT, INNER, etc)
				if (! empty($join['type'])) {
					$tmp .= $join['type'] . ' ';
				}
				// add the table name and condition
				$tmp .= 'JOIN ' . $join['name'];
				$tmp .= ' ON ' . $join['cond'];
				// add to the list
				$list[] = $tmp;
			}
			// add the list of all joins
			$stmt .= implode("\n\t", $list) . "\n";
		}
		
		// with these where conditions
		if ($parts['where']) {
			$stmt .= "WHERE\n\t";
			$stmt .= implode("\n\t", $parts['where']) . "\n";
		}
		
		// grouped by these columns
		if ($parts['group']) {
			$stmt .= "GROUP BY\n\t";
			$stmt .= implode(",\n\t", $parts['group']) . "\n";
		}
		
		// having these conditions
		if ($parts['having']) {
			$stmt .= "HAVING\n\t";
			$stmt .= implode("\n\t", $parts['having']) . "\n";
		}
		
		// ordered by these columns
		if ($parts['order']) {
			$stmt .= "ORDER BY\n\t";
			$stmt .= implode(",\n\t", $parts['order']) . "\n";
		}
		
		// done!
		return $stmt;
	}
}
?>