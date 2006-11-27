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
 * @version $Id$
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
    protected function _buildSelect($parts)
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
        $stmt = parent::_buildSelect($parts);
        
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
        $result = $this->query($cmd);
        $list = $result->fetchAll(PDO::FETCH_COLUMN, 0);
        return $list;
    }
    
    public function describeTable($table)
    {
        throw $this->_exception(
            'ERR_METHOD_NOT_IMPLEMENTED',
            array('method' => 'describeTable')
        );
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
    protected function _dropIndex($table, $name)
    {
        // http://www.w3schools.com/sql/sql_drop.asp
        $this->query("DROP INDEX {$table}.{$name}");
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
    protected function _createSequence($name, $start = 1)
    {
        $start = (int) $start;
        $this->query(
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
    protected function _dropSequence($name)
    {
        $this->query("DROP TABLE $name");
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
    protected function _nextSequence($name)
    {
        $cmd = "INSERT INTO $name DEFAULT VALUES";
        
        // first, try to increment the sequence number, assuming
        // the table exists.
        try {
            $this->query($cmd);
        } catch (Exception $e) {
            // error when updating the sequence.
            // assume we need to create it, then
            // try to increment again.
            $this->_createSequence($name);
            $this->query($cmd);
        }
        
        // get the sequence number
        $stmt = $this->query("SELECT @@IDENTITY FROM $name");
        $id = $stmt->fetchColumn(0);
        
        // now that we have a new sequence number, delete any earlier rows
        // to keep the table small.  should this be a trigger instead?
        $this->query("DELETE FROM $name WHERE id < $id");
        
        // return the sequence number
        return $id;
    }
}
?>