<?php
/**
 * 
 * Uses a random slave server for SELECT queries, and a master server for all
 * other queries.
 * 
 * Multiple slaves can be configured, but once we start reading from a slave,
 * we read from that slave for the remainder of the connection.  (Invoking
 * disconnect() will let you connect to new random slave.)
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
class Solar_Sql_Adapter_MysqlReplicated extends Solar_Sql_Adapter_Mysql
{
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are ...
     * 
     * `slaves`
     * : (array) An array of arrays, each representing the connection values
     *   for a different slave server.
     * 
     * The non-slave connection values are for the master server.
     * 
     * For example:
     * 
     * {{code: php
     *     $config = array(
     *     
     *         // these apply to the master and all slaves
     *         'profiling' => false,
     *         'cache'     => array('adapter' => 'Solar_Cache_Adapter_Var'),
     *         
     *         // master server connection
     *         'host'      => null,
     *         'port'      => null,
     *         'sock'      => null,
     *         'user'      => null,
     *         'pass'      => null,
     *         'name'      => null,
     *         
     *         // all slave servers
     *         'slaves'    => array(
     *             
     *             // first slave server
     *             0 => array(
     *                 'host'      => null,
     *                 'port'      => null,
     *                 'sock'      => null,
     *                 'user'      => null,
     *                 'pass'      => null,
     *                 'name'      => null,
     *             ),
     *             
     *             // second slave server
     *             1 => array(
     *                 'host'      => null,
     *                 'port'      => null,
     *                 'sock'      => null,
     *                 'user'      => null,
     *                 'pass'      => null,
     *                 'name'      => null,
     *             ),
     *             
     *             // ... etc ...
     *         ),
     *     );
     * }}
     *
     * @var array
     * 
     */
    protected $_Solar_Sql_Adapter_MysqlReplicated = array(
        'slaves' => array(),
    );
    
    /**
     * 
     * Which slave key the [[$_dsn]] property was built from.
     * 
     * @var mixed
     * 
     */
    protected $_dsn_key;
    
    /**
     * 
     * A PDO-style DSN for the master server.
     * 
     * The [[$_dsn]] property is for the slave server.
     * 
     * @var string
     * 
     */
    protected $_dsn_master;
    
    /**
     * 
     * A PDO object for accessing the master server.
     * 
     * The [[$_pdo]] property is for the slave server.
     * 
     * @var PDO
     * 
     * @see $_pdo
     * 
     */
    protected $_pdo_master;
    
    /**
     * 
     * Sets the connection-specific cache key prefix.
     * 
     * @param string $prefix The cache-key prefix.  When null, defaults to
     * the class name, a slash, and the md5() of the DSN **for the master**.
     * 
     * @return string
     * 
     */
    public function setCacheKeyPrefix($prefix = null)
    {
        if ($prefix === null) {
            $prefix = get_class($this) . '/' . md5($this->_dsn_master);
        }
        
        $this->_cache_key_prefix = $prefix;
    }
    
    /**
     * 
     * Get the master PDO connection object (connects to the database if 
     * needed).
     * 
     * @return PDO
     * 
     */
    public function getPdoMaster()
    {
        $this->connectMaster();
        return $this->_pdo_master;
    }
    
    /**
     * 
     * Sets the DSN for the slave and the master; the slave is picked at 
     * random from the list of slaves.
     * 
     * For example, "mysql:host=127.0.0.1;dbname=test"
     * 
     * For the keys 'port', 'user', 'pass', and 'name', if one is missing, it
     * gets set automatically from the master value. This lets you avoid some
     * repetition in your slave config setups, assuming that the values are 
     * the same for the master and slaves.
     * 
     * @return void
     * 
     * @see $_dsn
     * 
     * @see $_dsn_key
     * 
     * @see $_dsn_master
     * 
     */
    protected function _setDsn()
    {
        // pick a random slave key
        $this->_dsn_key = array_rand(array_keys($this->_config['slaves']));
        
        // get the slave info
        $slave = $this->_config['slaves'][$this->_dsn_key];
        
        // set missing information from the master
        $list = array('port', 'user', 'pass', 'name');
        foreach ($list as $item) {
            if (empty($slave[$item])) {
                $slave[$item] = $this->_config[$item];
            }
        }
        
        // set DSN for slave
        $this->_dsn = $this->_buildDsn($slave);
        
        // set DSN for master
        $this->_dsn_master = $this->_buildDsn($this->_config);
    }
    
    /**
     * 
     * Connects to a random slave server.
     * 
     * Does not re-connect if we already have active connections.
     * 
     * @return void
     * 
     */
    public function connect()
    {
        // already connected?
        if ($this->_pdo) {
            return;
        }
        
        // which slave dsn key was used?
        // need this so we have the right credentials.
        $key = $this->_dsn_key;
        
        // start profile time
        $time = microtime(true);
        
        // attempt the connection
        $this->_pdo = new PDO(
            $this->_dsn,
            $this->_config['slaves'][$key]['user'],
            $this->_config['slaves'][$key]['pass']
        );
        
        // post-connection tasks
        $this->_postConnect();
        
        // retain the profile data?
        $this->_addProfile($time, '__CONNECT');
    }
    
    /**
     * 
     * Connects to the master server.
     * 
     * Does not re-connect if we already have an active connection.
     * 
     * @return void
     * 
     */
    public function connectMaster()
    {
        // already connected?
        if ($this->_pdo_master) {
            return;
        }
        
        // start profile time
        $time = microtime(true);
        
        // attempt the connection
        $this->_pdo = new PDO(
            $this->_dsn,
            $this->_config['user'],
            $this->_config['pass']
        );
        
        // post-connection tasks
        $this->_postConnectMaster();
        
        // retain the profile data?
        $this->_addProfile($time, '__CONNECT_MASTER');
    }
    
    /**
     * 
     * Force the master connection to use the same attributes as the slave.
     * 
     * @return void
     * 
     */
    protected function _postConnectMaster()
    {
        // adapted from example at
        // <http://us.php.net/manual/en/pdo.getattribute.php>
        $attribs = array(
            'AUTOCOMMIT', 'ERRMODE', 'CASE', 'CLIENT_VERSION',
            'CONNECTION_STATUS', 'ORACLE_NULLS', 'PERSISTENT', 'PREFETCH',
            'SERVER_INFO', 'SERVER_VERSION', 'TIMEOUT',
        );
        
        foreach ($attribs as $attr) {
            $key = constant("PDO::ATTR_$attr");
            $val = $this->_pdo->getAttribute($key);
            $this->_pdo_master->setAttribute($key, $val);
        }
    }
    
    /**
     * 
     * Disconnects from the master and the slave.
     * 
     * @return void
     * 
     */
    public function disconnect()
    {
        parent::diconnect();
        $this->_pdo_master = null;
    }
    
    /**
     * 
     * Prepares an SQL query as a PDOStatement object, using the slave PDO
     * connection for all 'SELECT' queries, and the master PDO connection for
     * all other queries.
     * 
     * @param string $stmt The text of the SQL statement, optionally with
     * named placeholders.
     * 
     * @return PDOStatement
     * 
     */
    protected function _prepare($stmt)
    {
        // is it a SELECT statement?
        $stmt = ltrim($stmt);
        $is_select = strtoupper(substr($stmt, 0, 6)) == 'SELECT';
        
        // prepare the statment
        try {
            if ($is_select) {
                // slave
                $this->connect();
                $key = $this->_dsn_key;
                $config = $this->_config['slaves'][$key];
                $prep = $this->_pdo->prepare($stmt);
            } else {
                // master
                $this->connectMaster();
                $config = $this->_config;
                $prep = $this->_pdo_master->prepare($stmt);
            }
        } catch (PDOException $e) {
            // note that we use $config as set in the try block above
            throw $this->_exception(
                'ERR_PREPARE_FAILED',
                array(
                    'pdo_code'  => $e->getCode(),
                    'pdo_text'  => $e->getMessage(),
                    'host'      => $config['host'],
                    'port'      => $config['port'],
                    'user'      => $config['user'],
                    'name'      => $config['name'],
                    'stmt'      => $stmt,
                    'data'      => $data,
                    'pdo_trace' => $e->getTraceAsString(),
                )
            );
        }
        
        return $prep;
    }
    
    /**
     * 
     * Leave autocommit mode and begin a transaction **on the master**.
     * 
     * @return void
     * 
     */
    public function begin()
    {
        $this->connectMaster();
        $time = microtime(true);
        $result = $this->_pdo_master->beginTransaction();
        $this->_addProfile($time, '__BEGIN_MASTER');
        return $result;
    }
    
    /**
     * 
     * Commit a transaction and return to autocommit mode **on the master**.
     * 
     * @return void
     * 
     */
    public function commit()
    {
        $this->connectMaster();
        $time = microtime(true);
        $result = $this->_pdo_master->commit();
        $this->_addProfile($time, '__COMMIT_MASTER');
        return $result;
    }
    
    /**
     * 
     * Roll back a transaction and return to autocommit mode **on the master**.
     * 
     * @return void
     * 
     */
    public function rollback()
    {
        $this->connectMaster();
        $time = microtime(true);
        $result = $this->_pdo_master->rollBack();
        $this->_addProfile($time, '__ROLLBACK_MASTER');
        return $result;
    }
    
    /**
     * 
     * Get the last auto-incremented insert ID from the database.
     * 
     * @param string $table The table name on which the auto-increment occurred.
     * 
     * @param string $col The name of the auto-increment column.
     * 
     * @return int The last auto-increment ID value inserted to the database.
     * 
     */
    public function lastInsertId($table = null, $col = null)
    {
        $this->connectMaster();
        return $this->_pdo_master->lastInsertId();
    }
}
