<?php
/**
 * 
 * Abstract base class for specific RDBMS driver information.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
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
 * Abstract base class for specific RDBMS driver information.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
abstract class Solar_Sql_Driver extends Solar_Base {
    
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
    protected $_config = array(
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
    protected $_pdo = null;
    
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
    protected $_pdo_type = null;
    
    /**
     * 
     * Execute these commands directly, without preparation.
     * 
     * @access protected
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
     * @access protected
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
     * @access protected
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
        
        // force names to lower case
        $this->_pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
        
        /** @todo Are there other portability attribs to consider? */
        
        // always use exceptions.
        $this->_pdo->setAttribute(PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION);
    }
    
    /**
     * 
     * Leave autocommit mode and begin a transaction.
     * 
     * @access public
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
     * @access public
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
     * @access public
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
        $this->_connect();
        
        // what kind of command?
        $pos = strpos($stmt, ' ');
        $cmd = substr($stmt, 0, $pos);
        
        // execute
        if (in_array(strtoupper($cmd), $this->_direct)) {
            // execute directly
            return $this->_pdo->exec($stmt);
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
     * @access public
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
        throw $this->_exception(
            'ERR_METHOD_NOT_IMPLEMENTED',
            array('method' => __FUNCTION__)
        );
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
        throw $this->_exception(
            'ERR_METHOD_NOT_IMPLEMENTED',
            array('method' => __FUNCTION__)
        );
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
        throw $this->_exception(
            'ERR_METHOD_NOT_IMPLEMENTED',
            array('method' => __FUNCTION__)
        );
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
        throw $this->_exception(
            'ERR_METHOD_NOT_IMPLEMENTED',
            array('method' => __FUNCTION__)
        );
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
        return $this->_native;
    }
    
    /**
     * 
     * Builds a CREATE TABLE statment.
     * 
     * We use this so that certain drivers can append table types
     * to the creation statment (e.g. MySQL).
     * 
     * @access public
     * 
     * @param string $name The table name to create.
     * 
     * @param string $cols The column definitions.
     * 
     * @return mixed An SQL-safe quoted value.
     * 
     */
    public function buildCreateTable($name, $cols)
    {
        return "CREATE TABLE $name (\n$cols\n)";
    }
    
    /**
     * 
     * Build an SQL SELECT statement from its component parts.
     * 
     * We use this so that drivers can append or wrap with LIMIT
     * clauses or emulation.
     * 
     * @access public
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