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

/**
 * Authentication adapter class.
 */
Solar::loadClass('Solar_Auth_Adapter');

/**
 * 
 * Authenticate against an SQL database table.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @todo add support for email, name, uri retrieval
 * 
 */
class Solar_Auth_Adapter_Sql extends Solar_Auth_Adapter {
    
    /**
     * 
     * User-supplied configuration values.
     * 
     * Keys are:
     * 
     * : \\sql\\ : (string|array) How to get the SQL object.  If a string, is
     * treated as a Solar::registry() object name.  If array, treated as
     * config for a standalone Solar_Sql object.
     * 
     * : \\table\\ : (string) Name of the table holding authentication data.
     * 
     * : \\handle_col\\ : (string) Name of the column with the handle.
     * 
     * : \\passwd_col\\ : (string) Name of the column with the MD5-hashed passwd.
     * 
     * : \\salt\\ : (string) A salt prefix to make cracking passwords harder.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'sql'        => 'sql',
        'table'      => 'members',
        'handle_col' => 'handle',
        'passwd_col' => 'passwd',
        'salt'       => null
    );
    
    /**
     * 
     * Verifies a username handle and password.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    protected function _verify()
    {
        $handle = $this->_handle;
        $passwd = $this->_passwd;
        
        // get the dependency object of class Solar_Sql
        $obj = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // build the SQL statement
        $stmt = "SELECT COUNT({$this->_config['handle_col']})"
              . " FROM {$this->_config['table']}"
              . " WHERE {$this->_config['handle_col']} = :handle"
              . " AND {$this->_config['passwd_col']} = :passwd";
        
        // build the placeholder data
        $data = array(
            'handle' => $handle,
            'passwd' => md5($this->_config['salt'] . $passwd)
        );
        
        // get the results (a count of rows)
        $result = $obj->select('one', $stmt, $data);
        
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