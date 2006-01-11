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
     * Map of Solar generic column types to RDBMS native declarations.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    protected $_native = array(
        'bool'      => 'DECIMAL(1,0)',
        'char'      => 'CHAR(:size)',
        'varchar'   => 'VARCHAR(:size)',
        'smallint'  => 'SMALLINT',
        'int'       => 'INTEGER',
        'bigint'    => 'LONGINT',
        'numeric'   => 'DECIMAL(:size,:scope)',
        'float'     => 'DOUBLE PRECISION',
        'clob'      => 'CLOB',
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
    protected $_pdo_type = 'odbc';
    
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
     * Adds a LIMIT clause (or equivalent) to a SELECT statement.
     * 
     * @access protected
     * 
     * @param array $parts The SELECT statement parts.
     * 
     * @return void
     * 
     */
    public function buildSelect($parts)
    {
        // determine count
        $count = ! empty($parts['limit']['count'])
            ? (int) $parts['limit']['count']
            : 0;
        
        // determine offset
        $offset = ! empty($parts['limit']['offset'])
            ? (int) $parts['limit']['offset']
            : 0;
                
        // build the basic statement
        $stmt = parent::buildSelect($parts);
        
        // add limits?
        if ($count > 0) {
        
            // are we adding an offset as well?
            if ($offset > 0) {
                // yes
                $top = "TOP($offset,$count)";
            } else {
                // no, just a top
                $top = "TOP(0,$count)";
            }
            
            // put the TOP in place, depending on DISTINCT
            if ($parts['distinct']) {
                $top = "SELECT DISTINCT $top";
                $pos = 15; // SELECT DISTINCT
            } else {
                $top = "SELECT $top";
                $pos = 6; // SELECT
            }
            
            // replace "SELECT" with the new "SELECT TOP" clause
            return $top . substr($stmt, $pos);
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
        $cmd = 'SELECT "table_name" FROM information_schema.tables';
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