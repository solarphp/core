<?php
/**
 * 
 * Abstract base class for specific RDBMS adapters.
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
 * 
 * Abstract base class for specific RDBMS adapters.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
abstract class Solar_Sql_Adapter extends Solar_Base {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are:
     * 
     * : \\host\\ : (string) Host specification (typically 'localhost').
     * 
     * : \\port\\ : (string) Port number for the host name.
     * 
     * : \\user\\ : (string) Connect to the database as this username.
     * 
     * : \\pass\\ : (string) Password associated with the username.
     * 
     * : \\name\\ : (string) Database name (or file path, or TNS name).
     * 
     * : \\mode\\ : (string) For SQLite, an octal file mode.
     * 
     * @var array
     * 
     */
    protected $_Solar_Sql_Adapter = array(
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
     * @var object
     * 
     */
    protected $_pdo = null;
    
    /**
     * 
     * Map of Solar generic column types to RDBMS native declarations.
     * 
     * The available column types are:
     * 
     * : \\bool\\ : A true/false boolean, generally stored as an integer 1 or 0.
     * 
     * : \\char\\ : A fixed-length string of 1-255 characters.
     * 
     * : \\varchar\\ : A variable-length string of 1-255 characters.
     * 
     * : \\smallint\\ : A 2-byte integer, value range (-32767 ... +32768).
     * 
     * : \\int\\ : A 4-byte integer, value range (-2,147,483,648 ... +2,147,483,647).
     * 
     * : \\bigint\\ : An 8-byte integer, value range roughly (-9,223,372,036,854,780,000... +9,223,372,036,854,779,999).
     * 
     * : \\numeric\\ : A fixed-point decimal number.
     * 
     * : \\float\\ : A double-precision floating-point decimal number.
     * 
     * : \\clob\\ : A character large object with a size of up to 2,147,483,647 bytes (about 2 GB).
     * 
     * : \\date\\ : An ISO 8601 date stored as a 10-character string; e.g., '1979-11-07'.
     * 
     * : \\time\\ : An ISO 8601 time stored as an 8-character string; e.g., '12:34:56'.
     * 
     * : \\timestamp\\ : An ISO 8601 timestamp stored as a 19-character string (no zone offset); e.g., '1979-11-07T12:34:56'.
     * 
     * @var array
     * 
     */
    protected $_native = array(
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
     * The PDO adapter DSN type.
     * 
     * This might not be the same as the Solar adapter type.
     * 
     * @var string
     * 
     */
    protected $_pdo_type = null;
    
    /**
     * 
     * Execute these commands directly, without preparation.
     * 
     * @var array
     * 
     */
    protected $_direct = array('CREATE', 'ALTER', 'DROP');
    
    /**
     * 
     * Creates a PDO-style DSN.
     * 
     * E.g., "mysql:host=127.0.0.1;dbname=test"
     * 
     * @return string A PDO-style DSN.
     * 
     */
    protected function _dsn()
    {
        $dsn = array();
        
        if (! empty($this->_config['host'])) {
            $dsn[] = 'host=' . $this->_config['host'];
        }
        
        if (! empty($this->_config['name'])) {
            $dsn[] = 'dbname=' . $this->_config['name'];
        }
        
        return $this->_pdo_type . ':' . implode(';', $dsn);
    }
    
    /**
     * 
     * Creates a PDO object and connects to the database.
     * 
     * @return void
     * 
     */
    protected function _connect()
    {
        // if we already have a PDO object, no need to re-connect.
        if ($this->_pdo) {
            return;
        }
        
        // build a DSN
        $dsn = $this->_dsn();
        
        // create PDO object
        // attempt the connection
        $this->_pdo = new PDO(
            $dsn,
            $this->_config['user'],
            $this->_config['pass']
        );
        
        // always use exceptions.
        $this->_pdo->setAttribute(PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION);
            
        // force names to lower case
        $this->_pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
        
        /** @todo Are there other portability attribs to consider? */
    }
    
    /**
     * 
     * Leave autocommit mode and begin a transaction.
     * 
     * @return void
     * 
     */
    public function begin()
    {
        $this->_connect();
        return $this->_pdo->beginTransaction();
    }
    
    /**
     * 
     * Commit a transaction and return to autocommit mode.
     * 
     * @return void
     * 
     */
    public function commit()
    {
        $this->_connect();
        return $this->_pdo->commit();
    }
    
    /**
     * 
     * Roll back a transaction and return to autocommit mode.
     * 
     * @return void
     * 
     */
    public function rollback()
    {
        $this->_connect();
        return $this->_pdo->rollBack();
    }
    
    /**
     * 
     * Prepares and executes an SQL statement with bound data.
     * 
     * @param string $stmt The text of the SQL statement, with
     * placeholders.
     * 
     * @param array $data An associative array of data to bind to the
     * placeholders.
     * 
     * @return mixed A PDOStatement object, or a count of rows affected.
     * 
     */
    public function exec($stmt, $data = array())
    {
        // connect to the database if needed
        $this->_connect();
        
        // what kind of command?
        $pos = strpos($stmt, ' ');
        $cmd = substr($stmt, 0, $pos);
        
        // execute
        if (in_array(strtoupper($cmd), $this->_direct)) {
            // execute schema modifications directly
            $this->_pdo->exec($stmt);
            return true;
        } else {
            // prepare and execute
            $obj = $this->_pdo->prepare($stmt);
            $obj->execute((array) $data);
            return $obj;
        }
    }
    
    /**
     * 
     * Safely quotes a value for an SQL statement.
     * 
     * @param mixed $val The value to quote.
     * 
     * @return mixed An SQL-safe quoted value.
     * 
     */
    public function quote($val)
    {
        $this->_connect();
        return $this->_pdo->quote($val);
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
    abstract public function createSequence($name, $start = 1);
    
    /**
     * 
     * Drops a sequence.
     * 
     * @param string $name The sequence name to drop.
     * 
     * @return void
     * 
     */
    abstract public function dropSequence($name);
    
    /**
     * 
     * Gets the next sequence number; creates the sequence if needed.
     * 
     * @param string $name The sequence name to increment.
     * 
     * @return int The next sequence number.
     * 
     */
    abstract public function nextSequence($name);
    
    /**
     * 
     * Returns a list of database tables.
     * 
     * @return array A sequential array of table names in the database.
     * 
     */
    abstract public function listTables();
    
    /**
     * 
     * Drops an index.
     * 
     * @param string $table The table of the index.
     * 
     * @param string $name The index name.
     * 
     * @return void
     * 
     */
    abstract public function dropIndex($table, $name);
    
    /**
     * 
     * Returns a list of native column types.
     * 
     * @return array
     * 
     */
    public function nativeColTypes()
    {
        return $this->_native;
    }
    
    /**
     * 
     * Builds a CREATE TABLE command string.
     * 
     * We use this so that certain adapters can append table types
     * to the creation statment (e.g. MySQL).
     * 
     * @param string $name The table name to create.
     * 
     * @param string $cols The column definitions.
     * 
     * @return string A CREATE TABLE command string.
     * 
     */
    public function buildCreateTable($name, $cols)
    {
        return "CREATE TABLE $name (\n$cols\n)";
    }
    
    /**
     * 
     * Build an SQL SELECT command string from its component parts.
     * 
     * We use this so that adapters can append or wrap with LIMIT
     * clauses or emulation.
     * 
     * @param array $parts The parts of the SELECT statement, generally
     * from a Solar_Sql_Select object.
     * 
     * @return string An SQL SELECT command string.
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
            $stmt .= implode("\n", $list) . "\n";
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