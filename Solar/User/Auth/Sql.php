<?php

/**
 * 
 * Authenticate against an SQL database table.
 *
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_User
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
 * Authenticate against an SQL database table.
 *
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_User
 * 
 */

class Solar_User_Auth_Sql extends Solar_Base {
    
    
    /**
     * 
     * User-supplied configuration values.
     * 
     * Keys are:
     * 
     * sql => (string|array) How to get the SQL object.  If a string, is
     * treated as a Solar::shared() object name.  If array, treated as
     * config for a standalone Solar_Sql object.
     * 
     * table => (string) Name of the table holding authentication data.
     * 
     * username_col => (string) Name of the column with the username.
     * 
     * password_col => (string) Name of the column with the MD5-hashed password.
     * 
     * salt => (string) A salt prefix to make cracking passwords harder.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    
    protected $config = array(
        'sql'          => 'sql',
        'table'        => 'sc_user',
        'username_col' => 'user_id',
        'password_col' => 'passwd',
        'salt'         => null
    );
    
    
    /**
     * 
     * Validate a username and password.
     *
     * @param string $user Username to authenticate.
     * 
     * @param string $pass The plain-text password to use.
     * 
     * @return boolean|Solar_Error True on success, false on failure,
     * or a Solar_Error object if there was an SQL error.
     * 
     * 
     */
    
    public function valid($username, $password)
    {
        // get the SQL object
        if (is_string($this->config['sql'])) {
            // use a shared object.
            $obj = Solar::shared($this->config['sql']);
        } else {
            // instantiate a new object.
            $obj = Solar::object('Solar_Sql', $this->config['sql']);
        }
        
        // if there were errors, return.
        if (! $obj || Solar::isError($obj)) {
            return $obj;
        }
        
        // build the SQL statement
        $stmt  = "SELECT COUNT({$this->config['username_col']})";
        $stmt .= " FROM {$this->config['table']}";
        $stmt .= " WHERE {$this->config['username_col']} = :username";
        $stmt .= " AND {$this->config['password_col']} = :password";
        
        // build the placeholder data
        $data = array(
            'username' => $username,
            'password' => md5($this->config['salt'] . $password)
        );
        
        // get the results (a count of rows)
        $result = $obj->fetchOne($stmt, $data);
        
        // if we get back exactly 1 row, the user is authenticated;
        // otherwise, it's more or less than exactly 1 row, or it's an
        // error object.
        if ($result == 1) {
            return true;
        } else {
            return $result;
        }
    }
}

?>