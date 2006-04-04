<?php
/**
 * 
 * Authenticate against an SQL database table.
 * 
 * @category Solar
 * 
 * @package Solar_User
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
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
 * @package Solar_User
 * 
 */
class Solar_User_Auth_Sql extends Solar_Base {
    
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
     * Validate a handle and passwd.
     * 
     * @param string $handle Username handle to authenticate.
     * 
     * @param string $passwd The plain-text passwd to use.
     * 
     * @return bool True on success, false on failure.
     * 
     */
    public function valid($handle, $passwd)
    {
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