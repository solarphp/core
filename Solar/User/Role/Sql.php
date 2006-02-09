<?php
/**
 * 
 * Get user roles from an SQL database table.
 *
 * @category Solar
 * 
 * @package Solar_User
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
 * Get user roles from an SQL database table.
 *
 * @category Solar
 * 
 * @package Solar_User
 * 
 */
class Solar_User_Role_Sql extends Solar_Base {
    
    /**
     * 
     * User-supplied configuration values.
     * 
     * Keys are:
     * 
     * 'sql' => (string|array) A string Solar::registry() object name, or a 
     * Solar::factory() config array.
     * 
     * 'table' => (string) The table where roles are stored.
     * 
     * 'username_col' => (string) The column of usernames.
     * 
     * 'rolename_col' => (string) The column of roles.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'sql' => null,
        'table' => 'sc_user_role',
        'username_col' => 'user_id',
        'rolename_col' => 'role',
    );
    
    /**
     * 
     * Get the roles for a user.
     * 
     * @param string $user Username to get roles for.
     * 
     * @return array An array of roles discovered in LDAP.
     * 
     */
    public function fetch($username)
    {
        // get the dependency object of class Solar_Sql
        $obj = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // build the SQL statement
        $stmt =  "SELECT " . $this->_config['rolename_col'];
        $stmt .= " FROM " . $this->_config['table'];
        $stmt .= " WHERE " . $this->_config['username_col'];
        $stmt .= " = :username";
        
        // build the placeholder data
        $data = array(
            'username' => $username,
        );
        
        // get the results (a column of rows)
        $result = $obj->fetchCol($stmt, $data);
        
        // done!
        return $result;
    }
}
?>