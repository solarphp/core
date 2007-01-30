<?php
/**
 * 
 * Class for connecting to Microsoft SQL databases.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Mssql.php 1885 2006-10-02 14:06:29Z pmjones $
 * 
 */

/**
 * Abstract SQL adapter.
 */
Solar::loadClass('Solar_Sql_Adapter');

/**
 * 
 * Class for connecting to Microsoft SQL databases.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
class Solar_Sql_Adapter_Mssql extends Solar_Sql_Adapter {
    
    /**
     * 
     * Map of Solar generic column types to RDBMS native declarations.
     * 
     * http://msdn.microsoft.com/library/default.asp?url=/library/en-us/tsqlref/ts_da-db_7msw.asp
     * 
     * @var array
     * 
     */
    protected $_native = array(
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
     * The PDO adapter type.
     * 
     * @var string
     * 
     */
    protected $_pdo_type = 'dblib';
    
    /**
     * 
     * Adds a LIMIT clause (or equivalent) to a SELECT statement.
     * 
     * Still not great, but much more efficient than old way.
     * http://lists.bestpractical.com/pipermail/rt-devel/2005-June/007339.html
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
        if ($count) {
            
            // is there an offset?
            if (! $offset) {
                // no, so it's a simple TOP request
                if ($parts['distinct']) {
                    $top = "SELECT DISTINCT TOP $count";
                    $pos = 15; // SELECT DISTINCT
                } else {
                    $top = "SELECT TOP $count";
                    $pos = 6; // SELECT
                }
                
                // replace with the TOP clause, and done!
                return $top . substr($stmt, $pos);
            }
            
            // the total of the count **and** the offset, combined.
            // this will be used in the "internal" portion of the
            // hacked-up statement.
            $total = $count + $offset;
            
            // build the "real" order for the external portion.
            $order = implode(',', $parts['order']);
            
            // build a "reverse" order for the internal portion.
            $reverse = $order;
            $reverse = str_ireplace(" ASC",  " \xFF", $reverse);
            $reverse = str_ireplace(" DESC", " ASC",  $reverse);
            $reverse = str_ireplace(" \xFF", " DESC", $reverse);
            
            // create a main statement that replaces the SELECT with a
            // SELECT TOP
            if ($parts['distinct']) {
                $top = "SELECT DISTINCT TOP $total";
                $pos = 15; // SELECT DISTINCT
            } else {
                $top = "SELECT TOP $total";
                $pos = 6; // SELECT
            }
            $main = "\n$top" . substr($stmt, $pos) . "\n";
            
            // build the hacked-up statement.
            // do we really need the "as" aliases here?
            $stmt  = "SELECT * FROM (";
            $stmt .= "SELECT TOP $count * FROM ($main) AS solar_limit_rev ORDER BY $reverse";
            $stmt .= ") AS solar_limit ORDER BY $order";
        }
    }
    
    /**
     * 
     * Returns a list of database tables.
     * 
     * @return array The list of tables in the database.
     * 
     */
    public function listTables()
    {
        $cmd = "SELECT name FROM sysobjects WHERE type = 'U' ORDER BY name";
        $result = $this->exec($cmd);
        $list = $result->fetchAll(PDO::FETCH_COLUMN, 0);
        return $list;
    }
    
    /**
     * 
     * Drops an index.
     * 
     * @param string $table The table of the index.
     * 
     * @param string $name The full index name.
     * 
     * @return void
     * 
     */
    public function dropIndex($table, $name)
    {
        // http://www.w3schools.com/sql/sql_drop.asp
        $this->exec("DROP INDEX {$table}.{$name}");
    }
    
    /**
     * 
     * Creates a sequence, optionally starting at a certain number.
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
        $start = (int) $start;
        $this->exec(
            "CREATE TABLE $name (id INT NOT NULL " .
            "IDENTITY($start,1) PRIMARY KEY CLUSTERED)"
        );
    }
    
    /**
     * 
     * Drops a sequence.
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
     * @param string $name The sequence name.
     * 
     * @return int The next sequence number.
     * 
     */
    public function nextSequence($name)
    {
        $this->_connect();
        $cmd = "INSERT INTO $name DEFAULT VALUES";
        
        // first, try to increment the sequence number, assuming
        // the table exists.
        try {
            $this->_connect();
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
        $stmt = $this->_pdo->prepare("SELECT @@IDENTITY FROM $name");
        $stmt->execute();
        $id = $stmt->fetchColumn(0);
        
        // now that we have a new sequence number, delete any earlier rows
        // to keep the table small.  should this be a trigger instead?
        $this->exec("DELETE FROM $name WHERE id < $id");
        
        // return the sequence number
        return $id;
    }
}
?>