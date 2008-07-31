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
     * A PDO-style DSN for the slave server.
     * 
     * The [[$_dsn]] property is for the master server.
     * 
     * @var string
     * 
     */
    protected $_dsn_slave;
    
    /**
     * 
     * Which slave key the [[$_dsn_slave]] property was built from.
     * 
     * @var mixed
     * 
     */
    protected $_dsn_slave_key;
    
    /**
     * 
     * A PDO object for accessing the slave server.
     * 
     * The [[$_pdo]] property is for the master server.
     * 
     * @var PDO
     * 
     * @see $_pdo
     * 
     */
    protected $_pdo_slave;
    
    /**
     * 
     * Get the slave PDO connection object (connects to the database if 
     * needed).
     * 
     * @return PDO
     * 
     */
    public function getPdoSlave()
    {
        $this->connect();
        return $this->_pdo_slave;
    }
    
    /**
     * 
     * Sets the DSN for the slave to a random slave server.
     * 
     * For example, "mysql:host=127.0.0.1;dbname=test"
     * 
     * @return void
     * 
     * @see $_dsn_slave
     * 
     * @see $_dsn_slave_key
     * 
     */
    protected function _setDsnSlave()
    {
        // the dsn info
        $dsn = array();
        
        // pick a random slave from the list
        $key = array_rand($this->_config['slaves']);
        
        // convenience copy of the slave info
        $slave = $this->_config['slaves'][$key];
        
        // socket, or host-and-port? (can't use both.)
        if (! empty($slave['sock'])) {
            
            // use a socket
            $dsn[] = 'unix_socket=' . $slave['sock'];
            
        } else {
            
            // use host and port
            if (! empty($slave['host'])) {
                $dsn[] = 'host=' . $slave['host'];
            }
        
            if (! empty($slave['port'])) {
                $dsn[] = 'port=' . $slave['port'];
            }
            
        }
        
        // database name
        if (! empty($slave['name'])) {
            $dsn[] = 'dbname=' . $slave['name'];
        }
        
        // done, set values
        $this->_dsn_slave_key = $key;
        $this->_dsn_slave = $this->_pdo_type . ':' . implode(';', $dsn);
    }
    
    /**
     * 
     * Connects to the master server and a random slave server.
     * 
     * Does not re-connect if we already have active connections.
     * 
     * @return void
     * 
     */
    protected function connect()
    {
        // connect to master
        parent::connect();
        
        // connect to slave?
        if ($this->_pdo_slave) {
            return;
        }
        
        // set the slave dsn and key
        $this->_setDsnSlave();
        
        // which slave dsn key was used?
        // need this so we have the right credentials.
        $key = $this->_dsn_slave_key;
        
        // start profile time
        $time = microtime(true);
        
        // attempt the connection
        $this->_pdo_slave = new PDO(
            $this->_dsn_slave,
            $this->_config['slaves'][$key]['user'],
            $this->_config['slaves'][$key]['pass']
        );
        
        // post-connection tasks
        $this->_postConnectSlave();
        
        // retain the profile data?
        $this->_addProfile($time, '__CONNECT_SLAVE');
    }
    
    /**
     * 
     * Force the slave connection to use the same attributes as the master.
     * 
     * @return void
     * 
     */
    protected function _postConnectSlave()
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
            $this->_pdo_slave->setAttribute($key, $val);
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
        $this->_pdo_slave = null;
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
                // all SELECTs go to the slave.
                // keep config info in case of exception
                $key = $this->_dsn_slave_key;
                $config = $this->_config['slaves'][$key];
                // prepare the statement
                $prep = $this->_pdo_slave->prepare($stmt);
            } else {
                // all other commands go to the master.
                // keep config info in case of exception
                $config = $this->_config;
                // prepare the statement
                $prep = $this->_pdo->prepare($stmt);
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
}
