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
     * Keys are ...
     * 
     * `host`
     * : (string) Host specification (typically 'localhost').
     * 
     * `port`
     * : (string) Port number for the host name.
     * 
     * `user`
     * : (string) Connect to the database as this username.
     * 
     * `pass`
     * : (string) Password associated with the username.
     * 
     * `name`
     * : (string) Database name (or file path, or TNS name).
     * 
     * `profile`
     * : (bool) Turn on query profiling?
     * 
     * @var array
     * 
     */
    protected $_Solar_Sql_Adapter = array(
        'host'      => null,
        'port'      => null,
        'user'      => null,
        'pass'      => null,
        'name'      => null,
        'profiling' => false,
    );
    
    /**
     * 
     * Map of Solar generic column types to RDBMS native declarations.
     * 
     * The available column types are ...
     * 
     * `bool`
     * : A true/false boolean, generally stored as an integer 1 or 0.
     * 
     * `char`
     * : A fixed-length string of 1-255 characters.
     * 
     * `varchar`
     * : A variable-length string of 1-255 characters.
     * 
     * `smallint`
     * : A 2-byte integer, value range (-32767 ... +32768).
     * 
     * `int`
     * : A 4-byte integer, value range (-2,147,483,648 ... +2,147,483,647).
     * 
     * `bigint`
     * : An 8-byte integer, value range roughly (-9,223,372,036,854,780,000... +9,223,372,036,854,779,999).
     * 
     * `numeric`
     * : A fixed-point decimal number.
     * 
     * `float`
     * : A double-precision floating-point decimal number.
     * 
     * `clob`
     * : A character large object with a size of up to 2,147,483,647 bytes (about 2 GB).
     * 
     * `date`
     * : An ISO 8601 date stored as a 10-character string; for example, '1979-11-07'.
     * 
     * `time`
     * : An ISO 8601 time stored as an 8-character string; for example, '12:34:56'.
     * 
     * `timestamp`
     * : An ISO 8601 timestamp stored as a 19-character string (no zone offset); for example, '1979-11-07T12:34:56'.
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
        'timestamp' => null,
    );
    
    /**
     * 
     * Use these values to map native columns to Solar generic data types.
     * 
     * @var array
     * 
     */
    protected $_describe = array();
    
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
     * A quick-and-dirty query profile array.
     * 
     * Each element is an array, where the first value is the query execution
     * time in microseconds, and the second value is the query string.
     * 
     * Only populated when the `profile` config key is true.
     * 
     * @var array
     * 
     */
    protected $_profile = array();
    
    /**
     * 
     * Whether or not profiling is turned on.
     * 
     * @var bool
     * 
     */
    protected $_profiling = false;
    
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
        $this->setProfiling($this->_config['profiling']);
    }
    
    /**
     * 
     * Turns profiling on and off.
     * 
     * @param bool $flag True to turn profiling on, false to turn it off.
     * 
     * @return void
     * 
     */
    public function setProfiling($flag)
    {
        $this->_profiling = (bool) $flag;
    }
    
    /**
     * 
     * Get the query profile array.
     * 
     * @return array An array of queries executed by the adapter.
     * 
     */
    public function getProfile()
    {
        return $this->_profile;
    }
    
    /**
     * 
     * Get the PDO connection object (connects to the database if needed).
     * 
     * @return PDO
     * 
     */
    public function getPdo()
    {
        $this->_connect();
        return $this->_pdo;
    }
    
    
    // -----------------------------------------------------------------
    // 
    // Connection and basic queries
    // 
    // -----------------------------------------------------------------
    
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
        
        if (! empty($this->_config['host'])) {
            $dsn[] = 'host=' . $this->_config['host'];
        }
        
        if (! empty($this->_config['port'])) {
            $dsn[] = 'port=' . $this->_config['port'];
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
        
        // start profile time
        $before = microtime(true);
        
        // build a DSN
        $dsn = $this->_dsn();
        
        // create PDO object
        // attempt the connection
        $this->_pdo = new PDO(
            $dsn,
            $this->_config['user'],
            $this->_config['pass']
        );
        
        // always emulate prepared statements; this is faster, and works
        // better with CREATE, DROP, ALTER statements.  requires PHP 5.1.3
        // or later. note that we do this *first* (before using exceptions)
        // because not all adapters support it.
        $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        @$this->_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        
        // always use exceptions.
        $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        // force names to lower case
        $this->_pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
        
        // retain the profile data?
        if ($this->_profiling) {
            $after = microtime(true);
            $this->_profile[] = array($after - $before, '__CONNECT');
        }
        
    }
    
    /**
     * 
     * Prepares and executes an SQL statement with bound data.
     * 
     * This is the most-direct way to interact with the database; you simply
     * pass an SQL statement to the method, then the adapter uses
     * [[php::PDO | ]] to execute the statement and return a result.
     * 
     * {{code: php
     *     $sql = Solar::factory('Solar_Sql');
     * 
     *     // $result is a PDOStatement
     *     $result = $sql->query('SELECT * FROM table');
     * 
     *     // $result is a row-count
     *     $value = $sql->quote('something');
     *     $result = $sql->query("UPDATE table SET col = $value");
     * }}
     * 
     * To helper prevent SQL injection attacks, you should **always** quote
     * the values used in a direct query. Use [[quote()]], [[quoteInto()]],
     * or [[quoteMulti()]] to accomplish this.
     * 
     * Note that adapters provide convenience methods to automatically quote
     * values on common operations:
     * 
     * - [[Solar_Sql::insert()]]
     * - [[Solar_Sql::update()]]
     * - [[Solar_Sql::delete()]]
     * 
     * Additionally, the [[Class::Solar_Sql_Select | ]] class is dedicated to safely
     * creating portable SELECT statements, so you may wish to use that as well.
     * 
     * @param string $stmt The text of the SQL statement, with
     * placeholders.
     * 
     * @param array $data An associative array of data to bind to the
     * placeholders.
     * 
     * @return PDOStatement
     * 
     */
    public function query($stmt, $data = array())
    {
        $this->_connect();
        
        // begin the profile time
        $before = microtime(true);
        
        $obj = $this->_pdo->prepare($stmt);
        
        try {
            $obj->execute((array) $data);
        } catch (PDOException $e) {
            throw $this->_exception(
                'ERR_QUERY_FAILED',
                array(
                    'pdo_code' => $e->getCode(),
                    'pdo_text' => $e->getMessage(),
                    'host'     => $this->_config['host'],
                    'port'     => $this->_config['port'],
                    'user'     => $this->_config['user'],
                    'name'     => $this->_config['name'],
                    'stmt'     => $stmt,
                )
            );
        }
        
        // retain the profile data?
        if ($this->_profiling) {
            $after = microtime(true);
            $this->_profile[] = array($after - $before, $obj->queryString);
        }
        
        return $obj;
    }
    
    // -----------------------------------------------------------------
    // 
    // Manipulation
    // 
    // -----------------------------------------------------------------
    
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
        $before = microtime(true);
        $result = $this->_pdo->beginTransaction();
        if ($this->_profiling) {
            $after = microtime(true);
            $this->_profile[] = array($after - $before, "__BEGIN");
        }
        return $result;
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
        $before = microtime(true);
        $result = $this->_pdo->commit();
        if ($this->_profiling) {
            $after = microtime(true);
            $this->_profile[] = array($after - $before, "__COMMIT");
        }
        return $result;
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
        $before = microtime(true);
        $result = $this->_pdo->rollBack();
        if ($this->_profiling) {
            $after = microtime(true);
            $this->_profile[] = array($after - $before, "__ROLLBACK");
        }
        return $result;
    }
    
    /**
     * 
     * Inserts a row of data into a table.
     * 
     * Automatically applies Solar_Sql::quote() to the data values for you.
     * 
     * For example:
     * 
     * {{code: php
     *     $sql = Solar::factory('Solar_Sql');
     * 
     *     $table = 'invaders';
     *     $data = array(
     *         'foo' => 'bar',
     *         'baz' => 'dib',
     *         'zim' => 'gir'
     *     );
     * 
     *     $rows_affected = $sql->insert($table, $data);
     *     // calls 'INSERT INTO invaders (foo, baz, zim) VALUES ("bar", "dib", "gir")'
     * }}
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
        $result = $this->query($stmt, $data);
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
        $result = $this->query($stmt, $data);
        return $result->rowCount();
    }
    
    /**
     * 
     * Deletes rows from the table based on a WHERE clause.
     * 
     * For example ...
     * 
     * {{code: php
     *     $sql = Solar::factory('Solar_Sql');
     * 
     *     $table = 'events';
     *     $where = $sql->quoteInto('status = ?', 'cancelled');
     *     $rows_affected = $sql->delete($table, $where);
     * 
     *     // calls 'DELETE FROM events WHERE status = "cancelled"'
     * }}
     * 
     * For the $where parameter, you can also pass multiple WHERE conditions as
     * an array to be "AND"ed together.
     * 
     * {{code: php
     *     $sql = Solar::factory('Solar_Sql');
     * 
     *     $table = 'events';
     *     $where = array(
     *         "date >= ?"  => '2006-01-01',
     *         "date <= ?"  => '2006-01-31',
     *         "status = ?" => 'cancelled',
     *     );
     * 
     *     $rows_affected = $sql->delete($table, $where);
     * 
     *     // calls:
     *     // DELETE FROM events WHERE date >= "2006-01-01"
     *     // AND date <= "2006-01-31" AND status = "cancelled"
     * }}
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
        $result = $this->query("DELETE FROM $table WHERE $where");
        return $result->rowCount();
    }
    
    // -----------------------------------------------------------------
    // 
    // Retrieval
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Select rows from the database.
     * 
     * This method exists primarily in support of [[Class::Solar_Sql_Select | ]],
     * which you should strongly consider using instead of calling this method.
     * 
     * If you do use this method, here are some quick examples:
     * 
     * {{code: php
     *     $sql = Solar::factory('Solar_Sql');
     * 
     *     // get all rows
     *     $all = $sql->select('all', "SELECT * FROM table");
     * 
     *     // get just the first row
     *     $id = $sql->quote('id_value');
     *     $row = $sql->select('row', "SELECT * FROM table WHERE id = $id");
     * 
     *     // get just the first value
     *     $count = $sql->select('one', "SELECT COUNT(*) FROM table");
     * }}
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
     * @todo Deprecate in favor of fetch*() methods?
     * 
     */
    public function select($type, $spec, $data = array(), $class = null)
    {
        switch (strtolower($type)) {
        
        case 'all':
            if (empty($class)) {
                $class = 'Solar_Sql_Rowset';
            }
            return $this->fetchAll($spec, $data, $class);
            break;
        
        case 'array':
            // maintains BC
            // will be deprecated in favor of fetchAll()
            return $this->fetchAll($spec, $data, null);
            break;
        
        case 'assoc':
            // maintains BC
            // will change behavior to return Solar_Sql_Rowset (vice array)
            return $this->fetchAssoc($spec, $data, null);
            break;
        
        case 'col':
            return $this->fetchCol($spec, $data, $class);
            break;
        
        case 'one':
            return $this->fetchValue($spec, $data, $class);
            break;
        
        case 'pair':
        case 'pairs':
            return $this->fetchPairs($spec, $data, $class);
            break;
        
        case 'pdo':
        case 'pdostatement':
        case 'statement':
            // maintains BC
            // will be deprecated in favor of fetchResult()
            return $this->fetchResult($spec, $data, $class);
            break;
        
        case 'result':
            // maintains BC
            // will be deprecated and removed
            $result = $this->fetchResult($spec, $data, $class);
            $data = Solar::factory(
                'Solar_Sql_Result',
                array('PDOStatement' => $result)
            );
            return $data;
            break;
        
        case 'row':
            if (empty($class)) {
                $class = 'Solar_Sql_Row';
            }
            return $this->fetchOne($spec, $data, $class);
            break;
        
        case 'string':
            return $this->fetchString($spec, $data, $class);
            break;
        
        default:
            // not a recognized select type
            throw $this->_exception('ERR_SELECT_TYPE', array('type' => $type));
            break;
        }
    }
    
    /**
     * 
     * Fetches all rows from the database using sequential keys.
     * 
     * By default, returns as a Solar_Sql_Rowset object; however, if an empty
     * $class is specified, returns as a sequential array.
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @param string $class Use this class for the return object; default is
     * 'Solar_Sql_Rowset'.  If empty, or is 'array', returns as a sequential
     * array instead.
     * 
     * @return object
     * 
     */
    public function fetchAll($spec, $data = array(), $class = 'Solar_Sql_Rowset')
    {
        $result = $this->fetchResult($spec, $data);
        
        if ($class && strtolower($class) != 'array') {
            return Solar::factory(
                $class,
                array('data' => $result->fetchAll(PDO::FETCH_ASSOC))
            );
        } else {
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    /**
     * 
     * Fetches all rows from the database using associative keys (defined by
     * the first column).
     * 
     * By default, returns as a Solar_Sql_Rowset object; however, if an empty
     * $class is specified, returns as an associative array.
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @param string $class Use this class for the return object; default is
     * 'Solar_Sql_Rowset'.  If empty, or is 'array', returns an associative
     * array instead.
     * 
     * @return array
     * 
     */
    public function fetchAssoc($spec, $data = array(), $class = 'Solar_Sql_Rowset')
    {
        $result = $this->fetchResult($spec, $data);
        
        $data = array();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $key = current($row); // value of the first element
            $data[$key] = $row;
        }
        
        if ($class) {
            return Solar::factory(
                $class,
                array('data' => $data)
            );
        } else {
            return $data;
        }
    }
    
    /**
     * 
     * Fetches the first column of all rows as a sequential array.
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @return array
     * 
     */
    public function fetchCol($spec, $data = array())
    {
        $result = $this->fetchResult($spec, $data);
        return $result->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    
    /**
     * 
     * Fetches the very first value (i.e., first column of the first row).
     * 
     * When $spec is an array, automatically sets LIMIT 1 OFFSET 0 to limit
     * the results to a single row.
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @return mixed
     * 
     */
    public function fetchValue($spec, $data = array())
    {
        if (is_array($spec)) {
            // automatically limit to the first row only,
            // but leave the offset alone.
            $spec['limit']['count'] = 1;
        }
        $result = $this->fetchResult($spec, $data);
        return $result->fetchColumn(0);
    }
    
    /**
     * 
     * Fetches an associative array of all rows as key-value pairs (first 
     * column is the key, second column is the value).
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @return array
     * 
     */
    public function fetchPairs($spec, $data = array())
    {
        $result = $this->fetchResult($spec, $data);
        
        $data = array();
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $data[$row[0]] = $row[1];
        }
        
        return $data;
    }

    /**
     * 
     * Fetches a PDOStatement result object.
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @return array
     * 
     */
    public function fetchResult($spec, $data = array())
    {
        // build the statement from its component parts if needed
        if (is_array($spec)) {
            $stmt = $this->_buildSelect($spec);
        } else {
            $stmt = $spec;
        }
        
        // execute and get the PDOStatement result object
        return $this->query($stmt, $data);
    }
    
    /**
     * 
     * Fetches one row from the database.
     * 
     * By default, returns as a Solar_Sql_Row object; however, if an empty
     * $class is specified, returns as an associative array.
     * 
     * When $spec is an array, automatically sets LIMIT 1 OFFSET 0 to limit
     * the results to a single row.
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @param string $class Use this class for the return object; default is
     * 'Solar_Sql_Rowset'.  If empty or 'array', returns as a sequential
     * array instead.
     * 
     * @return object
     * 
     */
    public function fetchOne($spec, $data = array(), $class = 'Solar_Sql_Row')
    {
        if (is_array($spec)) {
            // automatically limit to the first row only,
            // but leave the offset alone.
            $spec['limit']['count'] = 1;
        }
        
        $result = $this->fetchResult($spec, $data);
        
        if ($class && strtolower($class) != 'array') {
            return Solar::factory(
                $class,
                array('data' => $result->fetch(PDO::FETCH_ASSOC))
            );
        } else {
            return $result->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    /**
     * 
     * Builds the SQL statement and returns it as a string instead of 
     * executing it.  Useful for debugging.
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @return string
     * 
     */
    public function fetchString($spec)
    {
        // build the statement from its component parts if needed
        if (is_array($spec)) {
            return $this->_buildSelect($spec);
        } else {
            return $spec;
        }
    }
    
    
    // -----------------------------------------------------------------
    // 
    // Sequences
    // 
    // -----------------------------------------------------------------
    
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
        return $this->_pdo->lastInsertId($name);
    }
    
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
        $result = $this->_createSequence($name, $start);
        return $result;
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
    abstract protected function _createSequence($name, $start = 1);
    
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
        $result = $this->_dropSequence($name);
        return $result;
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
    abstract protected function _dropSequence($name);
    
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
        $result = $this->_nextSequence($name);
        return $result;
    }
    
    /**
     * 
     * Gets the next sequence number; creates the sequence if needed.
     * 
     * @param string $name The sequence name to increment.
     * 
     * @return int The next sequence number.
     * 
     */
    abstract protected function _nextSequence($name);
    
    
    // -----------------------------------------------------------------
    // 
    // Table and columns discovery
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Returns a list of database tables.
     * 
     * @return array A sequential array of table names in the database.
     * 
     */
    abstract public function fetchTableList();
    
    /**
     * 
     * Returns an array describing the columns in a table.
     * 
     * @param string $table The table name to fetch columns for.
     * 
     * @return array An array of table columns.
     * 
     */
    abstract public function fetchTableCols($table);
    
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
            $stmt = $this->_buildCreateTable($table, $cols);
            $result = $this->query($stmt);
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
        return $this->query("DROP TABLE $table");
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
        return $this->query($stmt);
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
        return $this->query("ALTER TABLE $table DROP COLUMN $name");
    }
    
    /**
     * 
     * Creates a portable index on a table.
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
        return $this->query($cmd);
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
        return $this->_dropIndex($table, $fullname);
    }
    
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
    abstract protected function _dropIndex($table, $name);
    
    
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
            // quote array values, not keys, then combine with commas.
            foreach ($val as $k => $v) {
                $val[$k] = $this->quote($v);
            }
            return implode(', ', $val);
        } elseif (is_numeric($val)) {
            // no need to quote numerics
            return $val;
        } else {
            // quote all other scalars
            $this->_connect();
            return $this->_pdo->quote($val);
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
     * (for example ' AND '), default null.
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
        if (! array_key_exists($type, $this->_native)) {
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
                $coldef = str_replace(':size', $size, $this->_native[$type]);
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
                $this->_native[$type]
            );
            
            break;
        
        default:
            $coldef = $this->_native[$type];
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
    
    /**
     * 
     * Builds a CREATE TABLE command string.
     * 
     * We use this so that certain adapters can append table types
     * to the creation statment (for example MySQL).
     * 
     * @param string $name The table name to create.
     * 
     * @param string $cols The column definitions.
     * 
     * @return string A CREATE TABLE command string.
     * 
     */
    protected function _buildCreateTable($name, $cols)
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
    protected function _buildSelect($parts)
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
    
    /**
     * 
     * Given a column specification, parse into datatype, size, and 
     * decimal scope.
     * 
     * @param string $spec The column specification; for example, "VARCHAR(255)"
     * or "NUMERIC(10,2)".
     * 
     * @return array A sequential array of the column type, size, and scope.
     * 
     */
    protected function _getTypeSizeScope($spec)
    {
        $spec  = strtolower($spec);
        $type  = null;
        $size  = null;
        $scope = null;
        
        // find the parens, if any
        $pos = strpos($spec, '(');
        if ($pos === false) {
            // no parens, so no size or scope
            $type = $spec;
        } else {
            // find the type first.
            $type = substr($spec, 0, $pos);
            
            // there were parens, so there's at least a size.
            // remove parens to get the size.
            $size = trim(substr($spec, $pos), '()');
            
            // a comma in the size indicates a scope.
            $pos = strpos($size, ',');
            if ($pos !== false) {
                $scope = substr($size, $pos + 1);
                $size  = substr($size, 0, $pos);
            }
        }
        
        $type = $this->_getSolarType($type);
        return array($type, $size, $scope);
    }
    
    /**
     * 
     * Given a native column data type, finds the generic Solar data type.
     * 
     * @param string $type The native column data type.
     * 
     * @return string The generic Solar data type, if one maps to the native
     * type; otherwise, returns the native type unchanged.
     * 
     */
    protected function _getSolarType($type)
    {
        foreach ($this->_describe as $native => $solar) {
            if (strtolower($type) == strtolower($native)) {
                return $solar;
            }
        }
        return $type;
    }
}
