<?php
/**
 * 
 * Class for connecting to SQL databases and performing standard operations.
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
 * Class for connecting to SQL databases and performing standard operations.
 * 
 * @category Solar
 * 
 * @package Solar_Sql
 * 
 */
class Solar_Sql extends Solar_Base {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are ...
     * 
     * `adapter`
     * : (string) The adapter class to use, e.g. 'Solar_Sql_Adapter_Mysql'.
     * 
     * `config`
     * : (array) Construction-time config keys to pass to the adapter
     *   to override Solar.config.php values.  Default is null.
     * 
     * @var array
     * 
     */
    protected $_Solar_Sql = array(
        'adapter' => 'Solar_Sql_Adapter_Sqlite',
        'config'  => null,
    );
    
    /**
     * 
     * Object to use for a specific RDBMS behaviors.
     * 
     * @var object
     * 
     */
    protected $_adapter = null;
    
    /**
     * 
     * Max identifier lengths for table, column, and index names.
     * 
     * The total length cannot exceed 63 (the Postgres limit).
     * 
     * Reserve 3 chars for suffixes ("__i" for indexes, "__s" for
     * sequences).
     * 
     * Reserve 2 chars for table__index separator (again, because
     * Postgres needs unique names for indexes even on different tables).
     * 
     * This leaves 58 characters to split between table name and col/idx
     * name.  Figure tables need more "space", so they get 30 and
     * tables/indexes get 28.
     * 
     * @var array
     * 
     */
    protected $_len = array(
        'tbl' => 30,
        'col' => 28,
        'idx' => 28
    );
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // basic construction
        parent::__construct($config);
        
        // create the adapter object
        $this->_adapter = Solar::factory(
            $this->_config['adapter'],
            $this->_config['config']
        );
    }
    
    /**
     * 
     * Generic query executor.
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
    public function query($stmt, $data = array())
    {
        return $this->_adapter->exec($stmt, $data);
    }
    
    /**
     * 
     * Get the underlying adapter query profile.
     * 
     * @return array An array of queries executed by the adapter.
     * 
     */
    public function getProfile()
    {
        return $this->_adapter->profile;
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
        return $this->_adapter->begin();
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
        return $this->_adapter->commit();
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
        return $this->_adapter->rollback();
    }
    
    /**
     * 
     * Inserts a row of data into a table.
     * 
     * Automatically applies Solar_Sql::quote() to the data values for you.
     * 
     * @param string $table The table to insert data into.
     * 
     * @param array $data An associative array where the key is the column
     * name and the value is the value to insert for that column.
     * 
     * @return int The number of rows affected, typically 1.
     * 
     */
    public function insert($table, $data)
    {
        // the base statement
        $stmt = "INSERT INTO $table ";
        
        // field names come from the array keys
        $fields = array_keys($data);
        
        // add field names themselves
        $stmt .= '(' . implode(', ', $fields) . ') ';
        
        // add value placeholders
        $stmt .= 'VALUES (:' . implode(', :', $fields) . ')';
        
        // execute the statement
        $result = $this->_adapter->exec($stmt, $data);
        return $result->rowCount();
    }
    
    /**
     * 
     * Updates a table with specified data based on a WHERE clause.
     * 
     * Automatically applies Solar_Sql::quote() to the data values for you.
     * 
     * @param string $table The table to udpate.
     * 
     * @param array $data An associative array where the key is the column
     * name and the value is the value to use for that column.
     * 
     * @param string|array $where The SQL WHERE clause to limit which
     * rows are updated.
     * 
     * @return int The number of rows affected.
     * 
     */
    public function update($table, $data, $where)
    {
        // the base statement
        $stmt = "UPDATE $table SET ";
        
        // add "col = :col" pairs to the statement
        $tmp = array();
        foreach ($data as $col => $val) {
            $tmp[] = "$col = :$col";
        }
        $stmt .= implode(', ', $tmp);
        
        // add the where clause
        if ($where) {
            $where = $this->quoteMulti($where, ' AND ' );
            $stmt .= " WHERE $where";
        }
        
        // execute the statement
        $result = $this->_adapter->exec($stmt, $data);
        return $result->rowCount();
    }
    
    /**
     * 
     * Deletes rows from the table based on a WHERE clause.
     * 
     * @param string $table The table to delete from.
     * 
     * @param string|array $where The SQL WHERE clause to limit which
     * rows are deleted.
     * 
     * @return int The number of rows affected.
     * 
     */
    public function delete($table, $where)
    {
        if ($where) {
            $where = $this->quoteMulti($where, ' AND ');
        }
        $result = $this->_adapter->exec("DELETE FROM $table WHERE $where");
        return $result->rowCount();
    }
    
    /**
     * 
     * Select rows from the database.
     * 
     * Available selection types are ...
     * 
     * | $type    | returns 
     * | -------- | -----------------------------------------------------------------------------
     * | `all`    | Solar_Sql_Rowset object of all rows; return class can be set using $class 
     * | `array`  | A sequential array of all rows 
     * | `assoc`  | An assoc. array of all rows keyed on first column 
     * | `col`    | A sequential array of the first column of each row 
     * | `one`    | The first value in the first row 
     * | `pairs`  | An assoc. array of keys (first col) and values (second col) 
     * | `pdo`    | A PDOStatement object 
     * | `result` | A Solar_Sql_Result object 
     * | `row`    | A Solar_Sql_Row object of the first row; return class can be set using $class 
     * | `string` | The SQL SELECT command as a string 
     * 
     * @param string $type How to return the results.
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string (SELECT or non-select).
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @param string $class If selecting $type 'all' or 'row', use this
     * class for the return object.
     * 
     * @return mixed The query results for the return type requested.
     * 
     */
    public function select($type, $spec, $data = array(), $class = null)
    {
        // build the statement from its component parts if needed
        if (is_array($spec)) {
            $stmt = $this->_adapter->buildSelect($spec);
        } else {
            $stmt = $spec;
        }
        
        // are we just returning the statement?
        $lctype = strtolower($type);
        if ($lctype == 'string') {
            return $stmt;
        }
        
        // execute and get the PDOStatement result object
        $result = $this->_adapter->exec($stmt, $data);
        
        // return data based on the select type
        switch ($lctype) {
        
        // return Solar_Sql_Rowset object
        case 'all':
            if (empty($class)) {
                $class = 'Solar_Sql_Rowset';
            }
            $data = Solar::factory(
                $class,
                array('data' => $result->fetchAll(PDO::FETCH_ASSOC))
            );
            break;
        
        // return all as a sequential array
        case 'array':
            $data = $result->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        // return all as an array keyed on the first column
        case 'assoc':
            $data = array();
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $key = current($row); // value of the first element
                $data[$key] = $row;
            }
            break;
        
        // return the first col of every row
        case 'col':
            $data = $result->fetchAll(PDO::FETCH_COLUMN, 0);
            break;
            
        // return the first col of the first row
        case 'one':
            $data = $result->fetchColumn(0);
            break;
        
        // return data as key-value pairs where the first column
        // is the key and the second column is the value
        case 'pair':
        case 'pairs':
            $data = array();
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $data[$row[0]] = $row[1];
            }
            break;
        
        // the PDOStatement result object
        case 'pdo':
        case 'pdostatement':
        case 'statement':
            $data = $result;
            break;
            
        // a Solar_Sql_Result object
        case 'result':
            $data = Solar::factory(
                'Solar_Sql_Result',
                array('PDOStatement' => $result)
            );
            break;
        
        // return a Solar_Sql_Row object
        case 'row':
            if (empty($class)) {
                $class = 'Solar_Sql_Row';
            }
            $data = Solar::factory(
                $class,
                array('data' => $result->fetch(PDO::FETCH_ASSOC))
            );
            break;
        
        // not a recognized select type
        default:
            throw $this->_exception('ERR_SELECT_TYPE', array('type' => $type));
            break;
        }
        
        // done!
        return $data;
    }
    
    
    // -----------------------------------------------------------------
    // 
    // Sequences
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Creates a sequence in the database.
     * 
     * @param string $name The sequence name to create; this will be 
     * automatically suffixed with '__s' for portability reasons.
     * 
     * @param string $start The starting sequence number.
     * 
     * @return void
     * 
     * @todo Check name length.
     * 
     */
    public function createSequence($name, $start = 1)
    {
        $name .= '__s'; // we do this to deconflict in PostgreSQL
        $result = $this->_adapter->createSequence($name, $start);
        return $result;
    }
    
    /**
     * 
     * Drops a sequence from the database.
     * 
     * @param string $name The sequence name to drop; this will be 
     * automatically suffixed with '__s' for portability reasons.
     * 
     * @return void
     * 
     */
    public function dropSequence($name)
    {
        $name .= '__s'; // we do this to deconflict in PostgreSQL
        $result = $this->_adapter->dropSequence($name);
        return $result;
    }
    
    /**
     * 
     * Gets the next number in a sequence; creates the sequence if it does not exist.
     * 
     * @param string $name The sequence name; this will be 
     * automatically suffixed with '__s' for portability reasons.
     * 
     * @return int The next number in the sequence.
     * 
     */
    public function nextSequence($name)
    {
        $name .= '__s'; // we do this to deconflict in PostgreSQL
        $result = $this->_adapter->nextSequence($name);
        return $result;
    }
    
    
    // -----------------------------------------------------------------
    // 
    // Table, column, and index management
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Creates a portable table.
     * 
     * The $cols parameter should be in this format ...
     * 
     * {{code: php
     *     $cols = array(
     *       'fieldOne' => array(
     *         'type'    => bool|char|int|etc,
     *         'size'    => total length for char|varchar|numeric
     *         'scope'   => decimal places for numeric
     *         'require' => true|false,
     *       ),
     *       'fieldTwo' => array(...)
     *     );
     * }}
     * 
     * For available field types, see Solar_Sql_Adapter::$_native.
     * 
     * @param string $table The name of the table to create.
     * 
     * @param array $cols Array of columns to create.
     * 
     * @return string An SQL string.
     * 
     * @todo Instead of stacking errors, stack info, then throw in exception.
     * 
     */
    public function createTable($table, $cols)
    {
        // table name can only be so many chars
        $len = strlen($table);
        if ($len < 1 || $len > $this->_len['tbl']) {
            throw $this->_exception(
                'ERR_TABLE_NAME_LENGTH',
                array('table' => $table)
            );
        }
        
        // table name must be a valid word, and cannot end in
        // "__s" (this is to prevent sequence table collisions)
        if (! $this->_validIdentifier($table) || substr($table, -3) == "__s") {
            throw $this->_exception(
                'ERR_TABLE_NAME_RESERVED',
                array('table' => $table)
            );
        }
        
        // array of column definitions
        $coldef = array();
        
        // use this to stack errors when creating definitions
        $err = array();
        
        // loop through each column and get its definition
        foreach ($cols as $name => $info) {
            try {
                $result = $this->_buildColDef($name, $info);
                $coldef[] = "$name $result";
            } catch (Exception $e) {
                $err[$name] = $e->getInfo();
            }
        }
        
        // were there errors?
        if ($err) {
            throw $this->_exception(
                'ERR_TABLE_NOT_CREATED',
                $err
            );
        } else {
            // no errors, execute and return
            $cols = implode(",\n\t", $coldef);
            $stmt = $this->_adapter->buildCreateTable($table, $cols);
            $result = $this->_adapter->exec($stmt);
            return $result;
        }
    }
    
    /**
     * 
     * Drops a table from the database.
     * 
     * @param string $table The table name.
     * 
     * @return mixed
     * 
     */
    public function dropTable($table)
    {
        return $this->_adapter->exec("DROP TABLE $table");
    }
    
    /**
     * 
     * Returns a list of table names in the database.
     * 
     * @return array
     * 
     */
    public function listTables()
    {
        return $this->_adapter->listTables($this);
    }
    
    /**
     * 
     * Adds a portable column to a table in the database.
     * 
     * The $info parameter should be in this format ...
     * 
     * {{code: php
     *     $info = array(
     *         'type'    => bool|char|int|etc,
     *         'size'    => total length for char|varchar|numeric
     *         'scope'   => decimal places for numeric
     *         'require' => true|false,
     *     );
     * }}
     * 
     * @param string $table The table name (1-30 chars).
     * 
     * @param string $name The column name to add (1-28 chars).
     * 
     * @param array $info Information about the column.
     * 
     * @return mixed
     * 
     */
    public function addColumn($table, $name, $info)
    {
        $coldef = $this->_buildColDef($name, $info);
        $stmt = "ALTER TABLE $table ADD COLUMN $name $coldef";
        return $this->_adapter->exec($stmt);
    }
    
    /**
     * 
     * Drops a column from a table in the database.
     * 
     * @param string $table The table name.
     * 
     * @param string $name The column name to drop.
     * 
     * @return mixed
     * 
     */
    public function dropColumn($table, $name)
    {
        return $this->_adapter->exec("ALTER TABLE $table DROP COLUMN $name");
    }
    
    /**
     * 
     * Creates a portable index on a table.
     * 
     * The $info parameter should be in this format ...
     * 
     * {{code: php
     *     $type = 'normal';
     *     
     *     $info = array($type, 'col'); // single-col
     *     
     *     $info = array($type, array('col', 'col', 'col')), // multi-col
     *     
     *     $info = $type; // shorthand for single-col named for $name
     * }}
     * 
     * The $type may be 'normal' or 'unique'.
     * 
     * Indexes are automatically renamed to "tablename__indexname__i" for
     * portability reasons.
     * 
     * @param string $table The name of the table for the index (1-30 chars).
     * 
     * @param string $name The name of the index (1-28 chars).
     * 
     * @param bool $unique Whether or not the index is unique.
     * 
     * @param array $cols The columns in the index.  If empty, uses the
     * $name parameters as the column name.
     * 
     * @return void
     * 
     */
    public function createIndex($table, $name, $unique = false,
        $cols = null)
    {
        // are there any columns for the index?
        if (empty($cols)) {
            // take the column name from the index name
            $cols = $name;
        }
        
        // check the table name length
        $len = strlen($table);
        if ($len < 1 || $len > $this->_len['tbl']) {
            throw $this->_exception(
                'ERR_TABLE_NAME_LENGTH',
                array('table' => $table)
            );
        }
        
        // check the index name length
        $len = strlen($name);
        if ($len < 1 || $len > $this->_len['idx']) {
            throw $this->_exception(
                'ERR_IDX_NAME_LENGTH',
                array('table' => $table, 'index' => $name)
            );
        }
        
        // create a string of column names
        $cols = implode(', ', (array) $cols);
        
        // we prefix all index names with the table name,
        // and suffix all index names with '__i'.  this
        // is to soothe PostgreSQL, which demands that index
        // names not collide, even when they indexes are on
        // different tables.
        $fullname = $table . '__' . $name . '__i';
        
        // create index entry
        if ($unique) {
            $cmd = "CREATE UNIQUE INDEX $fullname ON $table ($cols)";
        } else {
            $cmd = "CREATE INDEX $fullname ON $table ($cols)";
        }
        return $this->_adapter->exec($cmd);
    }
    
    
    /**
     * 
     * Drops an index from a table in the database.
     * 
     * @param string $table The table name.
     * 
     * @param string $name The index name to drop.
     * 
     * @return mixed
     * 
     */
    public function dropIndex($table, $name)
    {
        $fullname = $table . '__' . $name . '__i';
        return $this->_adapter->dropIndex($table, $fullname);
    }
    
    
    // -----------------------------------------------------------------
    // 
    // Quoting
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Safely quotes a value for an SQL statement.
     * 
     * If an array is passed as the value, the array values are quoted
     * and then returned as a comma-separated string; this is useful 
     * for generating IN() lists.
     * 
     * {{code: php
     *     $sql = Solar::factory('Solar_Sql');
     *     
     *     $safe = $sql->quote('foo"bar"');
     *     // $safe == "'foo\"bar\"'"
     *     
     *     $safe = $sql->quote(array('one', 'two', 'three'));
     *     // $safe == "'one', 'two', 'three'"
     * }}
     * 
     * @param mixed $val The value to quote.
     * 
     * @return string An SQL-safe quoted value (or a string of 
     * separated-and-quoted values).
     * 
     */
    public function quote($val)
    {
        if (is_array($val)) {
            // quote array values, not keys, and only one level's worth
            // (i.e., non-recursive) ... then combine with commas.
            foreach ($val as $k => $v) {
                $val[$k] = $this->_adapter->quote($v);
            }
            return implode(', ', $val);
        } else {
            return $this->_adapter->quote($val);
        }
    }
    
    /**
     * 
     * Quotes a value and places into a piece of text at a placeholder.
     * 
     * The placeholder is a question-mark; all placeholders will be replaced
     * with the quoted value.   For example ...
     * 
     * {{code: php
     *     $sql = Solar::factory('Solar_Sql');
     *     
     *     $text = "WHERE date < ?";
     *     $date = "2005-01-02";
     *     $safe = $sql->quoteInto($text, $date);
     *     
     *     // $safe == "WHERE date < '2005-01-02'"
     * }}
     * 
     * @param string $txt The text with a placeholder.
     * 
     * @param mixed $val The value to quote.
     * 
     * @return mixed An SQL-safe quoted value (or string of separated values)
     * placed into the orignal text.
     * 
     */
    public function quoteInto($txt, $val)
    {
        $val = $this->quote($val);
        return str_replace('?', $val, $txt);
    }
    
    /**
     * 
     * Quote multiple text-and-value pieces.
     * 
     * The placeholder is a question-mark; all placeholders will be replaced
     * with the quoted value.   For example ...
     * 
     * {{code: php
     *     $sql = Solar::factory('Solar_Sql');
     *     
     *     $list = array(
     *          "WHERE date > ?"   => '2005-01-01',
     *          "  AND date < ?"   => '2005-02-01',
     *          "  AND type IN(?)" => array('a', 'b', 'c'),
     *     );
     *     $safe = $sql->quoteMulti($list);
     *     
     *     // $safe == "WHERE date > '2005-01-02' AND date < 2005-02-01 AND type IN('a','b','c')"
     * }}
     * 
     * @param array $list A series of key-value pairs where the key is
     * the placeholder text and the value is the value to be quoted into
     * it.  If the key is an integer, it is assumed that the value is
     * piece of literal text to be used and not quoted.
     * 
     * @param string $sep Return the list pieces separated with this string
     * (e.g. ' AND '), default null.
     * 
     * @return string An SQL-safe string composed of the list keys and
     * quoted values.
     * 
     */
    // rename to quoteIntoMany()?
    public function quoteMulti($list, $sep = null)
    {
        $text = array();
        foreach ((array) $list as $key => $val) {
            if (is_int($key)) {
                // integer $key means a literal phrase and no value to
                // be bound into it
                $text[] = $val;
            } else {
                // string $key means a phrase with a placeholder, and
                // $val should be bound into it.
                $text[] = $this->quoteInto($key, $val); 
            }
        }
        
        // return the condition list
        $result = implode($sep, $text);
        return $result;
    }
    
    
    // -----------------------------------------------------------------
    // 
    // Support
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Builds a column definition string.
     * 
     * The $info parameter should be in this format ...
     * 
     * $info = array(
     *   'type'    => bool|char|int|etc,
     *   'size'    => total length for char|varchar|numeric
     *   'scope'   => decimal places for numeric
     *   'require' => true|false,
     * );
     * 
     * @param string $name The column name.
     * 
     * @param array $info The column information.
     * 
     * @return string The column definition string.
     * 
     */
    protected function _buildColDef($name, $info)
    {
        // validate column name length
        $len = strlen($name);
        if ($len < 1 || $len > $this->_len['col']) {
            throw $this->_exception(
                'ERR_COL_NAME_LENGTH',
                array('col' => $name)
            );
        }
        
        // column name must be a valid word
        if (! $this->_validIdentifier($name)) {
            throw $this->_exception(
                'ERR_COL_NAME_RESERVED',
                array('col' => $name)
            );
        }
        
        // set default values for these variables
        $tmp = array(
            'type'     => null,
            'size'     => null,
            'scope'    => null,
            'require'  => null, // true means NOT NULL, false means NULL
        );
        
        $info = array_merge($tmp, $info);
        extract($info); // see array keys, above
        
        // force values
        $name    = trim(strtolower($name));
        $type    = strtolower(trim($type));
        $size    = (int) $size;
        $scope   = (int) $scope;
        $require = (bool) $require;
        
        // is it a recognized column type?
        $native = $this->_adapter->nativeColTypes();
        if (! array_key_exists($type, $native)) {
            throw $this->_exception(
                'ERR_COL_TYPE_UNKNOWN',
                array('col' => $name, 'type' => $type)
            );
        }
        
        // basic declaration string
        switch ($type) {
        
        case 'char':
        case 'varchar':
            // does it have a valid size?
            if ($size < 1 || $size > 255) {
                throw $this->_exception(
                    'ERR_COL_SIZE',
                    array('col' => $name, 'size' => $size)
                );
            } else {
                // replace the 'size' placeholder
                $coldef = str_replace(':size', $size, $native[$type]);
            }
            break;
        
        case 'numeric':
        
            if ($size < 1 || $size > 255) {
                throw $this->_exception(
                    'ERR_COL_SIZE',
                    array('col' => $name, 'size' => $size, 'scope' => $scope)
                );
            }
            
            if ($scope < 0 || $scope > $size) {
                throw $this->_exception(
                    'ERR_COL_SCOPE',
                    array('col' => $name, 'size' => $size, 'scope' => $scope)
                );
            }
            
            // replace the 'size' and 'scope' placeholders
            $coldef = str_replace(
                array(':size', ':scope'),
                array($size, $scope),
                $native[$type]
            );
            
            break;
        
        default:
            $coldef = $native[$type];
            break;
        
        }
        
        // set the "NULL"/"NOT NULL" portion
        $coldef .= ($require) ? ' NOT NULL' : ' NULL';
        
        // done
        return $coldef;
    }
    
    /**
     * 
     * Check if a table, column, or index name is a valid identifier.
     * 
     * @param string $word The word to check.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    protected function _validIdentifier($word)
    {
        static $reserved;
        if (! isset($reserved)) {
            $reserved = Solar::factory('Solar_Sql_Reserved');
        }
        
        // is it a reserved word?
        if (in_array(strtoupper($word), $reserved->words)) {
            return false;
        }
        
        // only a-z, 0-9, and _ are allowed in words.
        // must start with a letter, not a number or underscore.
        if (! preg_match('/^[a-z][a-z0-9_]*$/', $word)) {
            return false;
        }
        
        // must not have two or more underscores in a row
        if (strpos($word, '__') !== false) {
            return false;
        }
        
        // guess it's OK
        return true;
    }
}
?>