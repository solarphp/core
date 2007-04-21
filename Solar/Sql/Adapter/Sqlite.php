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
        'numeric'   => 'NUMERIC',
        'float'     => 'DOUBLE',
        'clob'      => 'CLOB',
        'date'      => 'DATE',
        'time'      => 'TIME',
        'timestamp' => 'TIMESTAMP'
    );
    
    /**
     * 
     * Map of native RDBMS types to Solar generic types used when reading 
     * table column information.
     * 
     * @var array
     * 
     * @see fetchTableCols()
     * 
     */
    protected $_native_solar = array(
        'BOOLEAN'   => 'bool',
        'BOOL'      => 'bool',
        'CHAR'      => 'char',
        'VARCHAR'   => 'varchar',
        'SMALLINT'  => 'smallint',
        'INTEGER'   => 'int',
        'INT'       => 'int',
        'BIGINT'    => 'bigint',
        'NUMERIC'   => 'numeric',
        'DOUBLE'    => 'float',
        'FLOAT'     => 'float',
        'REAL'      => 'float',
        'CLOB'      => 'clob',
        'DATE'      => 'date',
        'TIME'      => 'time',
        'TIMESTAMP' => 'timestamp',
        'DATETIME'  => 'timestamp',
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
     * For example, "mysql:host=127.0.0.1;dbname=test"
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
     * Returns a list of all tables in the database.
     * 
     * @return array All table names in the database.
     * 
     */
    public function fetchTableList()
    {
        // copied from PEAR DB
        $cmd = "SELECT name FROM sqlite_master WHERE type='table' " .
            "UNION ALL SELECT name FROM sqlite_temp_master " .
            "WHERE type='table' ORDER BY name";
        
        return $this->fetchCol($cmd);
    }
    
    /**
     * 
     * Describes the columns in a table.
     * 
     * @param string $table The table to describe.
     * 
     * @return array
     * 
     */
    public function fetchTableCols($table)
    {
        // sqlite> create table areas (id INTEGER PRIMARY KEY AUTOINCREMENT,
        //         name VARCHAR(32) NOT NULL);
        // sqlite> pragma table_info(areas);
        // cid |name |type        |notnull |dflt_value |pk
        // 0   |id   |INTEGER     |0       |           |1
        // 1   |name |VARCHAR(32) |99      |           |0
        
        // strip non-word characters to try and prevent SQL injections
        $table = preg_replace('/[^\w]/', '', $table);
        
        // where the description will be stored
        $descr = array();
        
        // get the CREATE TABLE sql; need this for finding autoincrement cols
        $create_table = $this->fetchValue(
            "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = :table",
            array('table' => $table)
        );
        
        // loop through the result rows; each describes a column.
        $cols = $this->fetchAll("PRAGMA TABLE_INFO($table)");
        foreach ($cols as $val) {
            $name = $val['name'];
            list($type, $size, $scope) = $this->_getTypeSizeScope($val['type']);
            
            // find autoincrement column in CREATE TABLE sql.
            // non-word char, followed by the col name, followed by "INTEGER
            // PRIMARY KEY AUTOINCREMENT", followed by non-word char
            $find = "/\W$name\s+INTEGER\s+PRIMARY\s+KEY\s+AUTOINCREMENT\W/Ui";
            $autoinc = preg_match(
                $find,
                $create_table,
                $matches
            );
            
            $descr[$name] = array(
                'name'    => $name,
                'type'    => $type,
                'size'    => ($size  ? (int) $size  : null),
                'scope'   => ($scope ? (int) $scope : null),
                'default' => $val['dflt_value'],
                'require' => (bool) ($val['notnull']),
                'primary' => (bool) ($val['pk'] == 1),
                'autoinc' => (bool) $autoinc,
            );
        }
        
        // For defaults using keywords, SQLite always reports the keyword
        // *value*, not the keyword itself (e.g., '2007-03-07' instead of
        // 'CURRENT_DATE').
        // 
        // The allowed keywords are CURRENT_DATE, CURRENT_TIME, and
        // CURRENT_TIMESTAMP.
        // 
        //   <http://www.sqlite.org/lang_createtable.html>
        // 
        // Check the table-creation SQL for the default value to see if it's
        // a keyword and report 'null' in those cases.
        
        // get the list of columns
        $cols = array_keys($descr);
        
        // how many are there?
        $last = count($cols) - 1;
        
        // loop through each column and find out if its default is a keyword
        foreach ($cols as $curr => $name) {
            
            // if there's no default value, there can't be a keyword.
            if (! $descr[$name]['default']) {
                continue;
            }
            
            // look for :curr_col :curr_type . DEFAULT CURRENT_(*)
            $find = $descr[$name]['name'] . '\s+'
                  . $this->_solar_native[$descr[$name]['type']]
                  . '.*\s+DEFAULT\s+CURRENT_';
            
            // if not at the end, don't look further than the next coldef
            if ($curr < $last) {
                $next = $cols[$curr + 1];
                $find .= '.*' . $descr[$next]['name'] . '\s+'
                       . $this->_solar_native[$descr[$next]['type']];
            }
            
            // is the default a keyword?
            preg_match("/$find/ims", $create_table, $matches);
            if (! empty($matches)) {
                $descr[$name]['default'] = null;
            }
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
        return $this->query("DROP TABLE $name");
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
        return $this->query("DROP INDEX $name");
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
            // forces datatype, primary key, and autoincrement
            $coldef = 'INTEGER PRIMARY KEY AUTOINCREMENT';
        } elseif ($primary) {
            $coldef .= ' PRIMARY KEY';
        }
    }
}
