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
	* The "parent" Solar_Sql object (needed for query execution).
	* 
	* @access protected
	* 
	* @var object
	* 
	*/
	
	protected $sql = null;
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	* @param array $config An array of user-defined config options.
	* 
	* @return void
	* 
	*/
	
	public function __construct($config = null)
	{
		parent::__construct($config);
		$this->sql = $this->config['sql'];
	}
	
	
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
		$stmt .= implode(',\n\t', $parts['cols']) . "\n";
		
		// from these tables
		$stmt .= "FROM\n\t";
		$stmt .= implode(",\n\t", $parts['from']) . "\n";
		
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
			$stmt .= implode("\n\t", $list, "\n");
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