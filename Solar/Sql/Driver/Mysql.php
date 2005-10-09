<?php

/**
* 
* Class for MySQL behaviors.
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
* Class for MySQL behaviors.
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
	* The PDO driver type.
	* 
	* @access protected
	* 
	* @var string
	* 
	*/
	
	protected $pdo_type = 'mysql';
	
	
	/**
	* 
	* Builds a SELECT statement from its component parts.
	* 
	* Adds LIMIT clause.
	* 
	* @access public
	* 
	* @param array $parts The component parts of the statement.
	* 
	* @return void
	* 
	*/
	
	public function buildSelect($parts)
	{
		// build the baseline statement
		$stmt = parent::buildSelect($parts);
		
		// determine count
		$count = ! empty($parts['limit']['count'])
			? (int) $parts['limit']['count']
			: 0;
		
		// determine offset
		$offset = ! empty($parts['limit']['offset'])
			? (int) $parts['limit']['offset']
			: 0;
			
		// add the count and offset
		if ($count > 0) {
			$stmt .= " LIMIT $count";
			if ($offset > 0) {
				$stmt .= " OFFSET $offset";
			}
		}
		
		// done!
		return $stmt;
	}
	
	
	/**
	* 
	* Returns a list of database tables.
	* 
	* @access public
	* 
	* @return array The list of tables in the database.
	* 
	*/
	
	public function listTables()
	{
		$result = $this->exec('SHOW TABLES');
		$list = $result->fetchAll(PDO_FETCH_COLUMN, 0);
		return $list;
	}
	
	
	public function buildCreateTable($name, $cols)
	{
		$stmt = parent::buildCreateTable($name, $cols);
		$stmt .= " TYPE=InnoDB";// for transactions
		return $stmt;
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
		$this->exec("CREATE TABLE $name (id INT NOT NULL)");
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
		$cmd = "UPDATE $name SET id = LAST_INSERT_ID(id+1)";
		
		// first, try to increment the sequence number, assuming
		// the table exists.
		try {
			$stmt = $this->pdo->prepare($cmd);
			$stmt->execute();
		} catch (Exception $e) {
			// error when updating the sequence.
			// assume we need to create it.
			$this->createSequence($name);
			
			// now try to increment again.
			$stmt = $this->pdo->prepare($cmd);
			$stmt->execute();
		}
		
		// get the sequence number
		return $this->pdo->lastInsertID();
	}
}
?>