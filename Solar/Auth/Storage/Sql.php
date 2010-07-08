<?php
/**
 * 
 * Authenticate against an SQL database table.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Auth_Storage_Sql extends Solar_Auth_Storage
{
    /**
     * 
     * Default configuration values.
     * 
     * @config dependency sql A Solar_Sql dependency injection.
     * 
     * @config string table Name of the table holding authentication data.
     * 
     * @config string handle_col Name of the column with the user handle ("username").
     * 
     * @config string passwd_col Name of the column with the MD5-hashed passwd.
     * 
     * @config string email_col Name of the column with the email address.
     * 
     * @config string moniker_col Name of the column with the display name (moniker).
     * 
     * @config string uri_col Name of the column with the website URI.
     * 
     * @config string uid_col Name of the column with the numeric user ID ("user_id").
     * 
     * @config string hash_algo The hashing algorithm for the password.  Default is 'md5'.
     *   See [[php::hash_algos() | ]] for a list of accepted algorithms.
     * 
     * @config string salt A salt prefix to make cracking passwords harder.
     * 
     * @config string|array where Additional _multiWhere() conditions to use
     *   when selecting rows for authentication.
     *
     * @config boolean Insert credentials into backend storage if verified by third
     *   party.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Storage_Sql = array(
        'sql'         => 'sql',
        'table'       => 'members',
        'handle_col'  => 'handle',
        'passwd_col'  => 'passwd',
        'email_col'   => null,
        'moniker_col' => null,
        'uri_col'     => null,
        'uid_col'     => null,
        'hash_algo'   => 'md5',
        'salt'        => null,
        'where'       => array(),
        'auto_create' => false,
    );

    /**
     * 
     * Return a list columns that represents the fetched user data
     *
     * @return array A list of columns to fetch.
     * 
     */
    protected function _getCols()
    {
        // list of optional columns as (property => field)
        $optional = array(
            'email'   => 'email_col',
            'moniker' => 'moniker_col',
            'uri'     => 'uri_col',
            'uid'     => 'uid_col',
        );
        
        // always get the user handle
        $cols = array($this->_config['handle_col']);
        
        // get optional columns
        foreach ($optional as $key => $val) {
            if ($this->_config[$val]) {
                $cols[] = $this->_config[$val];
            }
        }
        return $cols;
    }

    /**
     * 
     * Return a quoted reference to the handle column
     *
     * @return string Handle column
     * 
     */
    protected function _getHandleCol()
    {
        $handle_col = $this->_config['handle_col'];
        if (strpos($handle_col, '.') === false) {
            $handle_col = "{$this->_config['table']}.{$handle_col}";
        }
        return $handle_col;
    }

    /**
     * 
     * Return a quoted reference to the passwd column
     *
     * @return string Passwd column
     * 
     */
    protected function _getPasswdCol()
    {
        $passwd_col = $this->_config['passwd_col'];
        if (strpos($passwd_col, '.') === false) {
            $passwd_col = "{$this->_config['table']}.{$passwd_col}";
        }
        return $passwd_col;
    }

    /**
     * 
     * Convert a row loaded from the database into a set of auth
     * credentials
     * 
     */
    protected function _convertRow($row)
    {
        $info = array();
        $cols = array(
            'handle'  => 'handle_col',
            'email'   => 'email_col',
            'moniker' => 'moniker_col',
            'uri'     => 'uri_col',
            'uid'     => 'uid_col',
        );
        foreach ($cols as $key => $val) {
            if ($this->_config[$val]) {
                $info[$key] = $row[$this->_config[$val]];
            }
        }
        
        // done
        return $info;
    }
    
    /**
     * 
     * Stores a set of credentials
     *
     * @param array $credentials A list of credentials to store
     * 
     * @return mixed An array of verified user information, or boolean false
     * if verification failed.
     * 
     */
    public function _autoCreate($credentials)
    {
        $sql = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        $data = array();
        $cols = array(
            'handle'  => 'handle_col',
            'email'   => 'email_col',
            'moniker' => 'moniker_col',
            'uri'     => 'uri_col',
            );
        foreach ($cols as $key => $val) {
            if ($this->_config[$val] && !empty($credentials[$key])) {
                $data[$this->_config[$val]] = $credentials[$key];
            }
        }
        
        $data['status'] = 'active';
        
        $result = $sql->insert($this->_config['table'], $data);
        
        if ($result) {
            $credentials['uid'] = $sql->lastInsertId();
            return $credentials;
        } else {
            return false;
        }
    }

    /**
     * 
     * Load a user based on a set of credentials
     *
     * @param array $credentials A list of credentials to verify
     * 
     * @return mixed An array of verified user information, or boolean false
     * if verification failed.
     * 
     */
    protected function _loadUser($credentials)
    {
        // get the dependency object of class Solar_Sql
        $obj = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // get a selection tool using the dependency object
        $select = Solar::factory(
            'Solar_Sql_Select',
            array('sql' => $obj)
        );

        // build the select
        $select->from($this->_config['table'], $this->_getCols())
               ->where($this->_getHandleCol() . " = ?", $credentials['handle'])
               ->multiWhere($this->_config['where'])
               ->limit(1);

        $row = $select->fetchOne();
        if ($row) {
            return $this->_convertRow($row);
        } else {
            if ($this->_config['auto_create']) {
                return $this->_autoCreate($credentials);
            }
            return false;
        }
    }
    
    /**
     * 
     * Verifies set of credentials.
     *
     * @param array $credentials A list of credentials to verify
     * 
     * @return mixed An array of verified user information, or boolean false
     * if verification failed.
     * 
     */
    public function validateCredentials($credentials)
    {
        if (empty($credentials['handle'])) {
            return false;
        }
        if (empty($credentials['passwd'])) {

            // This is a password-less authentication
            if (!empty($credentials['verified'])) {
                return $this->_loadUser($credentials);
            }
            
            return false;
        }

        // get the dependency object of class Solar_Sql
        $obj = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // get a selection tool using the dependency object
        $select = Solar::factory(
            'Solar_Sql_Select',
            array('sql' => $obj)
        );
        
        // salt and hash the password
        $hash = hash(
            $this->_config['hash_algo'],
            $this->_config['salt'] . $credentials['passwd']
        );
        
        // build the select, fetch up to 2 rows (just in case there's actually
        // more than one, we don't want to select *all* of them).
        $select->from($this->_config['table'], $this->_getCols())
               ->where($this->_getHandleCol() . " = ?", $credentials['handle'])
               ->where($this->_getPasswdCol() . " = ?", $hash)
               ->multiWhere($this->_config['where'])
               ->limit(2);
               
        // get the results
        $rows = $select->fetchAll();
        
        // if we get back exactly 1 row, the user is authenticated;
        // otherwise, it's more or less than exactly 1 row.
        if (count($rows) == 1) {
            
            return $this->_convertRow(current($rows));
            
        } else {
        
            // User credentials are not valid
            return false;
        }
    }
}
