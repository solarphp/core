<?php
/**
 * 
 * Class for MySQL behaviors.
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
class Solar_Sql_Adapter_Mysql extends Solar_Sql_Adapter
{
    /**
     * 
     * Map of Solar generic types to RDBMS native types used when creating
     * portable tables.
     * 
     * @var array
     * 
     */
    protected $_solar_native = array(
        'bool'      => 'BOOLEAN',
        'char'      => 'CHAR',
        'varchar'   => 'VARCHAR',
        'smallint'  => 'SMALLINT',
        'int'       => 'INTEGER',
        'bigint'    => 'BIGINT',
        'numeric'   => 'DECIMAL',
        'float'     => 'DOUBLE',
        'clob'      => 'LONGTEXT',
        'date'      => 'DATE',
        'time'      => 'TIME',
        'timestamp' => 'DATETIME'
    );
    
    /**
     * 
     * Map of native RDBMS types to Solar generic types used when reading 
     * table column information.
     * 
     * Note that fetchTableCols() will programmatically convert TINYINT(1) to
     * 'bool' independent of this map.
     * 
     * @var array
     * 
     * @see fetchTableCols()
     * 
     */
    protected $_native_solar = array(
        
        // numeric
        'smallint'          => 'smallint',
        'int'               => 'int',
        'integer'           => 'int',
        'bigint'            => 'bigint',
        'dec'               => 'numeric',
        'decimal'           => 'numeric',
        'double'            => 'float',
        
        // date & time
        'date'              => 'date',
        'datetime'          => 'timestamp',
        'timestamp'         => 'int',
        'time'              => 'time',
        
        // string
        'national char'     => 'char',
        'nchar'             => 'char',
        'char'              => 'char',
        'binary'            => 'char',
        'national varchar'  => 'varchar',
        'nvarchar'          => 'varchar',
        'varchar'           => 'varchar',
        'varbinary'         => 'varchar',
        
        // clob
        'longtext'          => 'clob',
        'longblob'          => 'clob',
    );
        
    
    /**
     * 
     * The PDO adapter type.
     * 
     * @var string
     * 
     */
    protected $_pdo_type = 'mysql';
    
    /**
     * 
     * The quote character before an entity name (table, index, etc).
     * 
     * @var string
     * 
     */
    protected $_ident_quote_prefix = '`';
    
    /**
     * 
     * The quote character after an entity name (table, index, etc).
     * 
     * @var string
     * 
     */
    protected $_ident_quote_suffix = '`';
    
    /**
     * 
     * Creates a PDO-style DSN.
     * 
     * For example, "mysql:host=127.0.0.1;dbname=test"
     * 
     * @param array $info An array with host, post, name, etc. keys.
     * 
     * @return string A PDO-style DSN.
     * 
     */
    protected function _buildDsn($info)
    {
        // the dsn info
        $dsn = array();
        
        // socket, or host-and-port? (can't use both.)
        if (! empty($info['sock'])) {
            
            // use a socket
            $dsn[] = 'unix_socket=' . $info['sock'];
            
        } else {
            
            // use host and port
            if (! empty($info['host'])) {
                $dsn[] = 'host=' . $info['host'];
            }
        
            if (! empty($info['port'])) {
                $dsn[] = 'port=' . $info['port'];
            }
            
        }
        
        // database name
        if (! empty($info['name'])) {
            $dsn[] = 'dbname=' . $info['name'];
        }
        
        // done
        return $this->_pdo_type . ':' . implode(';', $dsn);
    }

    /**
     * 
     * Returns a list of all tables in the database.
     * 
     * @return array All table names in the database.
     * 
     */
    protected function _fetchTableList()
    {
        return $this->fetchCol('SHOW TABLES');
    }
    
    /**
     * 
     * Returns an array describing the columns in a table.
     * 
     * @param string $table The table name to fetch columns for.
     * 
     * @return array An array of table column information.
     * 
     */
    protected function _fetchTableCols($table)
    {
        // mysql> DESCRIBE table_name;
        // +--------------+--------------+------+-----+---------+-------+
        // | Field        | Type         | Null | Key | Default | Extra |
        // +--------------+--------------+------+-----+---------+-------+
        // | id           | int(11)      |      | PRI | 0       |       |
        // | created      | varchar(19)  | YES  | MUL | NULL    |       |
        // | updated      | varchar(19)  | YES  | MUL | NULL    |       |
        // | name         | varchar(127) |      | UNI |         |       |
        // | owner_handle | varchar(32)  | YES  | MUL | NULL    |       |
        // | subj         | varchar(255) | YES  |     | NULL    |       |
        // | prefs        | longtext     | YES  |     | NULL    |       |
        // +--------------+--------------+------+-----+---------+-------+
     
        // strip non-word characters to try and prevent SQL injections,
        // then quote it to avoid reserved-word issues
        $table = preg_replace('/[^\w]/', '', $table);
        $table = $this->quoteName($table);
        
        // where the description will be stored
        $descr = array();
        
        // loop through the result rows; each describes a column.
        $cols = $this->fetchAll("DESCRIBE $table");
        foreach ($cols as $val) {
            
            $name = $val['field'];
            
            // override $type to find tinyint(1) as boolean
            if (strtolower($val['type']) == 'tinyint(1)') {
                $type = 'bool';
                $size = null;
                $scope = null;
            } else {
                list($type, $size, $scope) = $this->_getTypeSizeScope($val['type']);
            }
            
            // save the column description
            $descr[$name] = array(
                'name'    => $name,
                'type'    => $type,
                'size'    => ($size  ? (int) $size  : null),
                'scope'   => ($scope ? (int) $scope : null),
                'default' => $this->_getDefault($val['default']),
                'require' => (bool) ($val['null'] != 'YES'),
                'primary' => (bool) ($val['key'] == 'PRI'),
                'autoinc' => (bool) (strpos($val['extra'], 'auto_increment') !== false),
            );
            
            // don't keep "size" for integers
            if (substr($type, -3) == 'int') {
                $descr[$name]['size'] = null;
            }
        }
            
        // done!
        return $descr;
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
        $upper = strtoupper($default);
        if ($upper == 'NULL' || $upper == 'CURRENT_TIMESTAMP') {
            // the only non-literal allowed by MySQL is "CURRENT_TIMESTAMP"
            return null;
        } else {
            // return the literal default
            return $default;
        }
    }
    
    /**
     * 
     * Builds a CREATE TABLE command string.
     * 
     * @param string $name The table name to create.
     * 
     * @param string $cols The column definitions.
     * 
     * @return string A CREATE TABLE command string.
     * 
     */
    protected function _sqlCreateTable($name, $cols)
    {
        $stmt = parent::_sqlCreateTable($name, $cols);
        $stmt .= " TYPE=InnoDB"; // for transactions
        $stmt .= " DEFAULT CHARSET=utf8 COLLATE=utf8_bin"; // for UTF8
        return $stmt;
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
        $table = $this->quoteName($table);
        $name = $this->quoteName($name);
        return $this->query("DROP INDEX $name ON $table");
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
        $name = $this->quoteName($name);
        $this->query("CREATE TABLE $name (id INT NOT NULL) TYPE=InnoDB");
        return $this->query("INSERT INTO $name (id) VALUES ($start)");
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
        $name = $this->quoteName($name);
        return $this->query("DROP TABLE IF EXISTS $name");
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
        $cmd = "UPDATE " . $this->quoteName($name)
             . " SET id = LAST_INSERT_ID(id+1)";
        
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
        return $this->_pdo->lastInsertID();
    }
    
    /**
     * 
     * Modifies the sequence name.
     * 
     * MySQL doesn't have sequences, so this adapter uses a table instead.
     * This means we have to deconflict between "real" tables and tables being
     * used for sequences, so this method appends "__s" to the sequnce name.
     * 
     * @param string $name The requested sequence name.
     * 
     * @return string The modified sequence name.
     * 
     */
    protected function _modSequenceName($name)
    {
        return $name . '__s';
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
            $coldef .= " AUTO_INCREMENT";
        }
        
        if ($primary) {
            $coldef .= " PRIMARY KEY";
        }
    }
}
