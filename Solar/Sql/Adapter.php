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
 * WHen writing an adapter, you need to override these abstract methods:
 * 
 * {{code: php
 *     abstract public function fetchTableList();
 *     abstract public function fetchTableCols($table);
 *     abstract protected function _createSequence($name, $start = 1);
 *     abstract protected function _dropSequence($name);
 *     abstract protected function _nextSequence($name);
 *     abstract protected function _dropIndex($table, $name);
 *     abstract protected function _modAutoincPrimary(&$coldef, $autoinc, $primary);
 * }}
 * 
 * Additionally, if backend does not have explicit "LIMIT ... OFFSET" support,
 * you will want to override _limitSelect($stmt, $parts) to rewrite the query
 * in order to emulate limit/select behavior.
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
     * `profiling`
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
     * Map of Solar generic types to RDBMS native types used when creating
     * portable tables.
     * 
     * See the individual adapters for specific mappings.
     * 
     * The available generic column types are ...
     * 
     * `char`
     * : A fixed-length string of 1-255 characters.
     * 
     * `varchar`
     * : A variable-length string of 1-255 characters.
     * 
     * `bool`
     * : A true/false boolean, generally stored as an integer 1 or 0.  May
     *   also be stored as null, allowing for ternary logic.
     * 
     * `smallint`
     * : A 2-byte integer in the range of -32767 ... +32768.
     * 
     * `int`
     * : A 4-byte integer in the range of -2,147,483,648 ... +2,147,483,647.
     * 
     * `bigint`
     * : An 8-byte integer, value range roughly (-9,223,372,036,854,780,000
     *   ... +9,223,372,036,854,779,999).
     * 
     * `numeric`
     * : A fixed-point decimal number of a specific size (total number of
     *   digits) and scope (the number of those digits to the right of the
     *   decimal point).
     * 
     * `float`
     * : A double-precision floating-point decimal number.
     * 
     * `clob`
     * : A character large object with a size of up to 2,147,483,647 bytes
     *   (about 2 GB).
     * 
     * `date`
     * : An ISO 8601 date; for example, '1979-11-07'.
     * 
     * `time`
     * : An ISO 8601 time; for example, '12:34:56'.
     * 
     * `timestamp`
     * : An ISO 8601 timestamp without a timezone offset; for example,
     *   '1979-11-07 12:34:56'.
     * 
     * @var array
     * 
     */
    protected $_solar_native = array(
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
     * Map of native RDBMS types to Solar generic types used when reading 
     * table column information.
     * 
     * See the individual adapters for specific mappings.
     * 
     * @var array
     * 
     * @see fetchTableCols()
     * 
     */
    protected $_native_solar = array();
    
    /**
     * 
     * A PDO object for accessing the RDBMS.
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
     * Max identifier lengths for table, column, and index names used when
     * creating portable tables.
     * 
     * The reasoning behind these numbers is as follows:
     * 
     * - The total length cannot exceed 63 (the Postgres limit).
     * 
     * - Reserve 3 chars for suffixes ("__i" for indexes, "__s" for
     *   sequences) because Postgres cannot have a table with the same name
     *   as an index or sequence.
     * 
     * - Reserve 2 chars for table__index separator, because Postgres needs
     *   needs unique names for indexes even on different tables.
     * 
     * This leaves 58 characters to split between table name and column/index
     * name.  I figure table names need more "space", so they get 30 chars,
     * and tables/indexes get 28.
     * 
     * @var array
     * 
     */
    protected $_len = array(
        'table' => 30,
        'col'   => 28,
        'index' => 28
    );
    
    /**
     * 
     * A quick-and-dirty query profile array.
     * 
     * Each element is an array, where the first value is the query execution
     * time in microseconds, and the second value is the query string.
     * 
     * Only populated when the `profiling` config key is true.
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
     * Prepares and executes an SQL statement, optionally binding values
     * to named parameters in the statement.
     * 
     * This is the most-direct way to interact with the database; you
     * pass an SQL statement to the method, then the adapter uses
     * [[php::PDO | ]] to execute the statement and return a result.
     * 
     * {{code: php
     *     $sql = Solar::factory('Solar_Sql');
     * 
     *     // $result is a PDOStatement
     *     $result = $sql->query('SELECT * FROM table');
     * }}
     * 
     * To help prevent SQL injection attacks, you should **always** quote
     * the values used in a direct query. Use [[quote()]], [[quoteInto()]],
     * or [[quoteMulti()]] to accomplish this. Even easier, use the automated
     * value binding provided by the query() method:
     * 
     * {{code: php
     *     // BAD AND SCARY:
     *     $result = $sql->query('SELECT * FROM table WHERE foo = $bar');
     *     
     *     // Much much better:
     *     $result = $sql->query(
     *         'SELECT * FROM table WHERE foo = :bar',
     *         array('bar' => $bar)
     *     );
     * }}
     * 
     * Note that adapters provide convenience methods to automatically quote
     * values on common operations:
     * 
     * - [[Solar_Sql::insert()]]
     * - [[Solar_Sql::update()]]
     * - [[Solar_Sql::delete()]]
     * 
     * Additionally, the [[Class::Solar_Sql_Select | ]] class is dedicated to
     * safely creating portable SELECT statements, so you may wish to use that
     * instead of writing literal SELECTs.
     * 
     * 
     * Automated Binding of Values in PHP 5.2.1 and Later
     * --------------------------------------------------
     * 
     * With PDO in PHP 5.2.1 and later, we can no longer just throw an array
     * of data at the statement for binding. We now need to bind values
     * specifically to their respective placeholders.
     * 
     * In addition, we can't bind one value to multiple identical named
     * placeholders; we need to bind that same value multiple times. So if
     * `:foo` is used three times, PDO uses `:foo` the first time, `:foo2` the
     * second time, and `:foo3` the third time.
     * 
     * This query() method examins the statement for all `:name` placeholders
     * and attempts to bind data from the `$data` array.  The regular-expression
     * it uses is a little braindead; it cannot tell if the :name placeholder
     * is literal text or really a place holder.
     * 
     * As such, you should *either* use the `$data` array for named-placeholder
     * value binding at query() time, *or* bind-as-you-go when building the 
     * statement, not both.  If you do, you are on your own to make sure
     * that nothing looking like a `:name` placeholder exists in the literal text.
     * 
     * Question-mark placeholders are not supported for automatic value
     * binding at query() time.
     * 
     * @param string $stmt The text of the SQL statement, optionally with
     * named placeholders.
     * 
     * @param array $data An associative array of data to bind to the named
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
        
        // prepare the statment
        $obj = $this->_pdo->prepare($stmt);
        
        // was data passed for binding?
        if ($data) {
            
            // find all :placeholder matches.  note that this is a little
            // brain-dead; it will find placeholders in literal text, which
            // will cause errors later.  so in general, you should *either*
            // bind at query time *or* bind as you go, not both.
            preg_match_all(
                "/\W:([a-zA-Z_][a-zA-Z0-9_]+?)\W/m",
                $stmt . "\n",
                $matches
            );
            
            // bind values to placeholders, repeating as needed
            $repeat = array();
            foreach ($matches[1] as $key) {
                
                // only attempt to bind if the data key exists.
                // this allows for nulls and empty strings.
                if (! array_key_exists($key, $data)) {
                    // skip it
                    continue;
                }
            
                // what does PDO expect as the placeholder name?
                if (empty($repeat[$key])) {
                    // first time is ":foo"
                    $repeat[$key] = 1;
                    $name = $key;
                } else {
                    // repeated times of ":foo" are treated by PDO as
                    // ":foo2", ":foo3", etc.
                    $repeat[$key] ++;
                    $name = $key . $repeat[$key];
                }
                
                // bind the value to the placeholder name
                $obj->bindValue($name, $data[$key]);
            }
        }
        
        // now try to execute
        try {
            $obj->execute();
        } catch (PDOException $e) {
            throw $this->_exception(
                'ERR_QUERY_FAILED',
                array(
                    'pdo_code'  => $e->getCode(),
                    'pdo_text'  => $e->getMessage(),
                    'host'      => $this->_config['host'],
                    'port'      => $this->_config['port'],
                    'user'      => $this->_config['user'],
                    'name'      => $this->_config['name'],
                    'stmt'      => $stmt,
                    'pdo_trace' => $e->getTraceAsString(),
                )
            );
        }
        
        // retain the profile data?
        if ($this->_profiling) {
            $after = microtime(true);
            $this->_profile[] = array($after - $before, $obj->queryString);
        }
        
        // done!
        return $obj;
    }
    
    // -----------------------------------------------------------------
    // 
    // Transactions
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
    
    // -----------------------------------------------------------------
    // 
    // Manipulation
    // 
    // -----------------------------------------------------------------
    
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
     * Fetches all rows from the database using sequential keys.
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
    public function fetchAll($spec, $data = array())
    {
        $result = $this->fetchPdo($spec, $data);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 
     * Fetches all rows from the database using associative keys (defined by
     * the first column).
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
    public function fetchAssoc($spec, $data = array())
    {
        $result = $this->fetchPdo($spec, $data);
        
        $data = array();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $key = current($row); // value of the first element
            $data[$key] = $row;
        }
        
        return $data;
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
        $result = $this->fetchPdo($spec, $data);
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
        $result = $this->fetchPdo($spec, $data);
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
        $result = $this->fetchPdo($spec, $data);
        
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
     * @return PDOStatement
     * 
     */
    public function fetchPdo($spec, $data = array())
    {
        // build the statement from its component parts if needed
        if (is_array($spec)) {
            $stmt = $this->_select($spec);
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
     * When $spec is an array, automatically sets LIMIT 1 OFFSET 0 to limit
     * the results to a single row.
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
    public function fetchOne($spec, $data = array())
    {
        if (is_array($spec)) {
            // automatically limit to the first row only,
            // but leave the offset alone.
            $spec['limit']['count'] = 1;
        }
        
        $result = $this->fetchPdo($spec, $data);
        return $result->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 
     * DEPRECATED: Fetch one row as a Solar_Sql_Row object.
     * 
     * This method is provided as a transitional aid while moving from Table
     * to Model.
     * 
     * @deprecated
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @param string $class The class name of the object to return; default is
     * 'Solar_Sql_Row'.
     * 
     * @return Solar_Sql_Row
     * 
     */
    public function fetchRow($spec, $data = array(), $class = null)
    {
        if (! $class) {
            $class = 'Solar_Sql_Row';
        }
        $result = $this->fetchOne($spec, $data);
        return Solar::factory($class, array('data' => $result));
    }
    
    /**
     * 
     * DEPRECATED: Fetch all rows as a Solar_Sql_Rowset object.
     * 
     * This method is provided as a transitional aid while moving from Table
     * to Model.
     * 
     * @deprecated
     * 
     * @param array|string $spec An array of component parts for a
     * SELECT, or a literal query string.
     * 
     * @param array $data An associative array of data to bind into the
     * SELECT statement.
     * 
     * @param string $class The class name of the object to return; default is
     * 'Solar_Sql_Rowset'.
     * 
     * @return Solar_Sql_Rowset
     * 
     */
    public function fetchRowset($spec, $data = array(), $class = null)
    {
        if (! $class) {
            $class = 'Solar_Sql_Rowset';
        }
        $result = $this->fetchAll($spec, $data);
        return Solar::factory($class, array('data' => $result));
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
    public function fetchSql($spec)
    {
        // build the statement from its component parts if needed
        if (is_array($spec)) {
            return $this->_select($spec);
        } else {
            return $spec;
        }
    }
    
    /**
     * 
     * Returns a SELECT statement built from its component parts.
     * 
     * @param array $parts The component parts of the SELECT.
     * 
     * @return string The SELECT string.
     * 
     */
    protected function _select($parts)
    {
        $stmt = $this->_sqlSelect($parts);
        $this->_modSelect($stmt, $parts);
        return $stmt;
    }
    
    /**
     * 
     * Builds the base SELECT command string from its component parts, without
     * the LIMIT portions; those are left to the individual adapters.
     * 
     * @param array $parts The parts of the SELECT statement, generally
     * from a Solar_Sql_Select object.
     * 
     * @return string A SELECT command string.
     * 
     */
    protected function _sqlSelect($parts)
    {
        $default = array(
            'distinct' => false,
            'cols'     => array(),
            'from'     => array(),
            'where'    => array(),
            'group'    => array(),
            'having'   => array(),
            'order'    => array(),
        );
        
        $parts = array_merge($default, $parts);
        
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
     * Modifies a SELECT statement in place to add a LIMIT clause.
     * 
     * The default code adds a LIMIT for MySQL, PostgreSQL, and Sqlite, but
     * adapters can override as needed.
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
      
        // add the count and offset
        if ($count > 0) {
            $stmt .= " LIMIT $count";
            if ($offset > 0) {
                $stmt .= " OFFSET $offset";
            }
        }
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
     *     // $safe = "WHERE date > '2005-01-02'
     *     //          AND date < 2005-02-01
     *     //          AND type IN('a','b','c')"
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
    // Auto-increment and sequence reading.
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
    // Table and column information reading.
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
    
    /**
     * 
     * Given a column specification, parse into datatype, size, and 
     * decimal scope.
     * 
     * @param string $spec The column specification; for example,
     * "VARCHAR(255)" or "NUMERIC(10,2)".
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
        
        foreach ($this->_native_solar as $native => $solar) {
            // $type is already lowered
            if ($type == strtolower($native)) {
                $type = strtolower($solar);
                break;
            }
        }
        
        return array($type, $size, $scope);
    }
    
    
    // -----------------------------------------------------------------
    // 
    // Table, column, index, and sequence management.
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
     *         'type'    => (string) bool, char, int, ...
     *         'size'    => (int) total length for char|varchar|numeric
     *         'scope'   => (int) decimal places for numeric
     *         'default' => (bool) the default value, if any
     *         'require' => (bool) is the value required to be NOT NULL?
     *         'primary' => (bool) is this a primary key column?
     *         'autoinc' => (bool) is this an auto-increment column?
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
        $stmt = $this->_sqlCreateTable($table, $cols);
        $this->query($stmt);
    }
    
    /**
     * 
     * Returns a CREATE TABLE command string for the adapter.
     * 
     * We use this so that certain adapters can append table types
     * to the creation statment (for example MySQL).
     * 
     * @param string $table The table name to create.
     * 
     * @param string $cols The column definitions.
     * 
     * @return string A CREATE TABLE command string.
     * 
     */
    protected function _sqlCreateTable($table, $cols)
    {
        // table name can only be so many chars
        $len = strlen($table);
        if ($len < 1 || $len > $this->_len['table']) {
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
                $coldef[] = $this->_sqlColdef($name, $info);
            } catch (Exception $e) {
                $err[$name] = $e->getInfo();
            }
        }
        
        if ($err) {
            throw $this->_exception(
                'ERR_CREATE_TABLE',
                $err
            );
        }
        
        // no errors, build a return the CREATE statement
        $cols = implode(",\n\t", $coldef);
        return "CREATE TABLE $table (\n\t$cols\n)";
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
     *         'type'    => (string) bool, char, int, ...
     *         'size'    => (int) total length for char|varchar|numeric
     *         'scope'   => (int) decimal places for numeric
     *         'default' => (bool) the default value, if any
     *         'require' => (bool) is the value required to be NOT NULL?
     *         'primary' => (bool) is this a primary key column?
     *         'autoinc' => (bool) is this an auto-increment column?
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
        $coldef = $this->_sqlColdef($name, $info);
        $stmt = "ALTER TABLE $table ADD COLUMN $coldef";
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
    public function createIndex($table, $name, $unique = false, $cols = null)
    {
        // are there any columns for the index?
        if (empty($cols)) {
            // take the column name from the index name
            $cols = $name;
        }
        
        // check the table name length
        $len = strlen($table);
        if ($len < 1 || $len > $this->_len['table']) {
            throw $this->_exception(
                'ERR_TABLE_NAME_LENGTH',
                array('table' => $table)
            );
        }
        
        // check the index name length
        $len = strlen($name);
        if ($len < 1 || $len > $this->_len['index']) {
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
    
    
    // -----------------------------------------------------------------
    // 
    // Support
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Returns a column definition string.
     * 
     * The $info parameter should be in this format ...
     * 
     * {{code: php
     *     $info = array(
     *         'type'    => (string) bool, char, int, ...
     *         'size'    => (int) total length for char|varchar|numeric
     *         'scope'   => (int) decimal places for numeric
     *         'default' => (bool) the default value, if any
     *         'require' => (bool) is the value required to be NOT NULL?
     *         'primary' => (bool) is this a primary key column?
     *         'autoinc' => (bool) is this an auto-increment column?
     *     );
     * }}
     * 
     * @param string $name The column name.
     * 
     * @param array $info The column information.
     * 
     * @return string The column definition string.
     * 
     */
    protected function _sqlColdef($name, $info)
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
        
        // short-form of definition
        if (is_string($info)) {
            $info = array('type' => $info);
        }
        
        // set default values for these variables
        $tmp = array(
            'type'    => null,
            'size'    => null,
            'scope'   => null,
            'default' => null,
            'require' => null,
            'primary' => false,
            'autoinc' => false,
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
        if (! array_key_exists($type, $this->_solar_native)) {
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
                $coldef = $this->_solar_native[$type] . "($size)";
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
            $coldef = $this->_solar_native[$type] . "($size,$scope)";
            
            break;
        
        default:
            $coldef = $this->_solar_native[$type];
            break;
        
        }
        
        // set the "NULL"/"NOT NULL" portion
        $coldef .= ($require) ? ' NOT NULL' : ' NULL';
        
        // modify with auto-increment and primary-key portions
        $this->_modAutoincPrimary($coldef, $autoinc, $primary);
        
        // done
        return "$name $coldef";
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
    abstract protected function _modAutoincPrimary(&$coldef, $autoinc, $primary);
    
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
