<?php

/**
 * 
 * Class for connecting to SQLite databases.
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
 * Class for connecting to SQLite databases.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_Sql
 * 
 */

class Solar_Sql_Driver_Sqlite extends Solar_Sql_Driver {
    
    
    /**
     * 
     * Map of Solar generic column types to RDBMS native declarations.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    
    protected $_native = array(
        'bool'      => 'BOOLEAN',
        'char'      => 'CHAR(:size)',
        'varchar'   => 'VARCHAR(:size)',
        'smallint'  => 'SMALLINT',
        'int'       => 'INTEGER',
        'bigint'    => 'BIGINT',
        'numeric'   => 'NUMERIC(:size,:scope)',
        'float'     => 'DOUBLE',
        'clob'      => 'CLOB',
        'date'      => 'DATE',
        'time'      => 'TIME',
        'timestamp' => 'TIMESTAMP'
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
    
    protected $_pdo_type = 'mysql';
    
    
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
        $cmd = "SELECT name FROM sqlite_master WHERE type='table' " .
            "UNION ALL SELECT name FROM sqlite_temp_master " .
            "WHERE type='table' ORDER BY name";
        
        $result = $this->exec($cmd);
        $list = $result->fetchAll(PDO::FETCH_COLUMN, 0);
        return $list;
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
        $cmd = "INSERT INTO $name (id) VALUES (NULL)";
        
        // first, try to increment the sequence number, assuming
        // the table exists.
        try {
            $stmt = $this->_pdo->prepare($cmd);
            $stmt->execute();
        } catch (Exception $e) {
            // error when updating the sequence.
            // assume we need to create it.
            $this->createSequence($name);
            
            // now try to increment again.
            $stmt = $this->_pdo->prepare($cmd);
            $stmt->execute();
        }
        
        // get the sequence number
        return $this->_pdo->lastInsertID();
    }
}
?>