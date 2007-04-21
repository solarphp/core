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
 * @author Stefan Bogdan <stefan_bogdan_daniel@yahoo.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
 * @package Solar_Sql
 * 
 * @todo Transaction support
 * 
 * @todo Better date/time creation
 * 
 * @todo Build tests
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
        'char'      => 'CHAR',
        'varchar'   => 'VARCHAR',
        'smallint'  => 'SMALLINT',
        'int'       => 'INTEGER',
        'bigint'    => 'BIGINT',
        'numeric'   => 'DECIMAL',
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
     * Default is 'dblib', but on Windows systems it is 'mssql'.
     * 
     * @var string
     * 
     */
    protected $_pdo_type = 'dblib';
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // if we're on windows, use the 'mssql' PDO type
    	if (substr(PHP_OS, 0, 3) == 'WIN') {
    	    $this->_pdo_type = 'mssql';
    	}
    }
    
    /**
     * 
     * Creates a PDO-style DSN.
     * 
     * For example, "mysql:host=127.0.0.1;dbname=test"
     * 
     * @return string A PDO-style DSN.
     * 
     */
    protected function _dsn()
    {
        // the default build process
        if ($this->_pdo_type != 'mssql') {
            return parent::_dsn();
        }
        
        // special build process for 'mssql' pdo type on Windows
        $dsn = array();
        
        // the host
        if (! empty($this->_config['host'])) {
            $tmp = 'host=' . $this->_config['host'];
            // the port
            if (! empty($this->_config['port'])) {
    		    $tmp = $tmp . ',' . $this->_config['port'];
            }
            $dsn[] = $tmp;
        }
        
        // database name
        if (! empty($this->_config['name'])) {
            $dsn[] = 'dbname=' . $this->_config['name'];
        }
	    
	    // done!
        return $this->_pdo_type . ':' . implode(';', $dsn);
    }
    
    /**
     * 
     * Modifies a SELECT statement in place to add a LIMIT clause.
     * 
     * <http://lists.bestpractical.com/pipermail/rt-devel/2005-June/007339.html>
     * 
     * @param string &$stmt The SELECT statement.
     * 
     * @param array &$parts The orignal SELECT component parts, in case the
     * adapter needs them.
     * 
     * @return void
     * 
     */
    protected function _modSelect(&$stmt, &$parts)
    {
        // determine count
        $count = ! empty($parts['limit']['count'])
            ? (int) $parts['limit']['count']
            : 0;
        
        // determine offset
        $offset = ! empty($parts['limit']['offset'])
            ? (int) $parts['limit']['offset']
            : 0;
        
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
     * Returns a list of all tables in the database.
     * 
     * @return array All table names in the database.
     * 
     */
    public function fetchTableList()
    {
        $cmd = "SELECT name FROM sysobjects WHERE type = 'U' ORDER BY name";
        $result = $this->query($cmd);
        $list = $result->fetchAll(PDO::FETCH_COLUMN, 0);
        return $list;
    }
    
    /**
     * 
     * Returns an array describing the columns in a table.
     * 
     * @param string $table The table name to fetch columns for.
     * 
     * @return array An array of table columns.
     * 
     */
    public function fetchTableCols($table)
    {
 	    $cmd = "
     	    SELECT
                C.COLUMN_NAME AS name, 
                C.DATA_TYPE AS type,
                C.IS_NULLABLE AS allow_nulls,
                C.NUMERIC_PRECISION AS size,
                C.NUMERIC_SCALE AS scope,
                (SELECT 't'
                    FROM
                        information_schema.key_column_usage AS k,
                	    information_schema.table_constraints AS tc 
                	WHERE
                	    tc.constraint_name = k.constraint_name
                	    AND tc.constraint_type = 'PRIMARY KEY'
                	    AND k.table_name = :table
                	    AND k.column_name  = c.COLUMN_NAME
                ) AS primary_key,
                c.COLUMN_DEFAULT,
                COLUMNPROPERTY( OBJECT_ID('tabela'), C.COLUMN_NAME, 'IsIdentity') AS autoinc
            FROM INFORMATION_SCHEMA.Tables T
            JOIN INFORMATION_SCHEMA.Columns AS C ON T.TABLE_NAME = C.TABLE_NAME
            WHERE
                T.TABLE_NAME NOT LIKE 'sys%'
                AND T.TABLE_NAME = :table
        ";
        
        // where the description will be stored
        $descr = array();
        
        // loop through the result rows; each describes a column.
        $cols = $this->fetchAll($cmd, array('table' => $table));
        foreach ($cols as $val) {
            $name = $val['name'];
            list($type, $size, $scope) = $this->_getTypeSizeScope($val['type']);
            $descr[$name] = array(
                'name'    => $name,
                'type'    => $type,
                'size'    => $val['size'],
                'scope'   => $val['scope'],
                'default' => $this->_getDefault($val['column_default']),
                'require' => (bool) ($val['allow_nulls'] == 'NO'),
                'primary' => (bool) ($val['primary_key'] == 't'),
                'autoinc' => (bool) ($val['autoinc'] == 1),
            );
        }
        
        // done
        return $descr;
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
     * Get the last auto-incremented insert ID from the database.
     * 
     * @param string $name The name of the auto-increment series; optional,
     * not normally required.
     * 
     * @return int The last auto-increment ID value inserted to the database.
     * 
     */
    public function lastInsertId($name = null)
    {
        $this->_connect();
	    return $this->fetchValue('SELECT @@IDENTITY AS id');
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
    
    /**
     * 
     * Given a native column SQL default value, finds a PHP literal value.
     * 
     * SQL NULLs are converted to PHP nulls.  Non-literal values (such as
     * keywords and functions) are also returned as null.
     * 
     * @param string $default The column default SQL value.
     * 
     * @return scalar A literal PHP value.
     * 
     */
    protected function _getDefault($default)
    {
    	$default = str_replace(
    	    array('(', ')'),
    	    '',
    	    $default
    	);
    	
        // numeric literal?
        if (is_numeric($default)) {
            return $default;
        }
	
    	if ($default == NULL) {
    		return null;
    	}
    	
    	return $default;
    }
    
    /**
     * 
     * Given a column definition, modifies the auto-increment and primary-key
     * clauses in place.
     * 
     * @param string &$coldef The column definition as it is now.
     * 
     * @param bool $autoinc Whether or not this is an auto-increment column.
     * 
     * @param bool $primary Whether or not this is a primary-key column.
     * 
     * @return void
     * 
     */
    protected function _modAutoincPrimary(&$coldef, $autoinc, $primary)
    {
        if ($autoinc) {
            $coldef .= " IDENTITY";
        }
        
        if ($primary) {
            $coldef .= " PRIMARY KEY";
        }
    }
}
