<?php
/**
 * 
 * Class for connecting to SQLite databases.
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
 * Class for connecting to SQLite databases.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
class Solar_Sql_Adapter_Sqlite extends Solar_Sql_Adapter {
    
    /**
     * 
     * Map of Solar generic column types to RDBMS native declarations.
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
     * Use these values to map native columns to Solar generic data types.
     * 
     * @var array
     * 
     */
    protected $_describe = array(
        'BOOLEAN'   => 'bool',
        'CHAR'      => 'char',
        'VARCHAR'   => 'varchar',
        'SMALLINT'  => 'smallint',
        'INTEGER'   => 'int',
        'BIGINT'    => 'bigint',
        'NUMERIC'   => 'numeric',
        'DOUBLE'    => 'float',
        'CLOB'      => 'clob',
        'DATE'      => 'date',
        'TIME'      => 'time',
        'TIMESTAMP' => 'timestamp',
    );
    
    /**
     * 
     * The PDO adapter type.
     * 
     * @var string
     * 
     */
    protected $_pdo_type = 'sqlite';
    
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
        if (! empty($this->_config['name'])) {
            $dsn[] = $this->_config['name'];
        }
        return $this->_pdo_type . ':' . implode(';', $dsn);
    }
    
    /**
     * 
     * Builds a SELECT statement from its component parts.
     * 
     * Adds LIMIT clause.
     * 
     * @param array $parts The component parts of the statement.
     * 
     * @return void
     * 
     */
    protected function _buildSelect($parts)
    {
        // build the baseline statement
        $stmt = parent::_buildSelect($parts);
        
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
     * @return string The SQL statement.
     * 
     */
    public function listTables()
    {
        // copied from PEAR DB
        $cmd = "SELECT name FROM sqlite_master WHERE type='table' " .
            "UNION ALL SELECT name FROM sqlite_temp_master " .
            "WHERE type='table' ORDER BY name";
        
        $result = $this->query($cmd);
        $list = $result->fetchAll(PDO::FETCH_COLUMN, 0);
        return $list;
    }
    
    /**
     * 
     * Describes the columns in a table.
     * 
     *     sqlite> create table areas (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(32) NOT NULL);
     *     sqlite> pragma table_info(areas);
     *     cid |name |type        |notnull |dflt_value |pk
     *     0   |id   |INTEGER     |0       |           |1
     *     1   |name |VARCHAR(32) |99      |           |0
     * 
     * @param string $table The table to describe.
     * 
     * @return array
     * 
     * @todo: For $autoinc, replace with preg() to allow for multiple spaces.
     * 
     */
    public function describeTable($table)
    {
        // strip non-word characters to try and prevent SQL injections
        $table = preg_replace('/[^\w]/', '', $table);
        
        // get the native PDOStatement result
        $result = $this->query("PRAGMA TABLE_INFO($table)");
        
        // where the description will be stored
        $descr = array();
        
        // loop through the result rows; each describes a column.
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $val) {
            $name = $val['name'];
            list($type, $size, $scope) = $this->_getTypeSizeScope($val['type']);
            $autoinc = strpos(strtoupper($val['type']), 'INTEGER PRIMARY KEY AUTOINCREMENT');
            $descr[$name] = array(
                'name'    => $name,
                'type'    => $type,
                'size'    => $size,
                'scope'   => $scope,
                'default' => $this->_getDefault($val['dflt_value']),
                'require' => (bool) ($val['notnull']),
                'primary' => (bool) ($val['pk'] == 1),
                'autoinc' => (bool) ($autoinc !== false),
            );
        }
            
        // done!
        return $descr;
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
        $start -= 1;
        $this->query("CREATE TABLE $name (id INTEGER PRIMARY KEY)");
        $this->query("INSERT INTO $name (id) VALUES ($start)");
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
        $this->query("DROP INDEX $name");
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
        $cmd = "INSERT INTO $name (id) VALUES (NULL)";
        
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
        return $this->lastInsertId();
    }
}
?>