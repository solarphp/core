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
     * : \\sql\\ : (dependency) A Solar_Sql dependency.
     * 
     * : \\table\\ : (string) The table where roles are stored.
     * 
     * : \\handle_col\\ : (string) The column for user handles.
     * 
     * : \\role_col\\ : (string) The column for roles.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'sql'        => 'sql',
        'table'      => 'member_roles',
        'handle_col' => 'handle',
        'role_col'   => 'role',
    );
    
    /**
     * 
     * Fetches the roles for a user.
     * 
     * @param string $handle User handle to get roles for.
     * 
     * @return array An array of roles discovered in the table.
     * 
     */
    public function fetch($handle)
    {
        // get the dependency object of class Solar_Sql
        $obj = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // build the SQL statement
        $stmt =  "SELECT " . $this->_config['role_col']
              .  " FROM " . $this->_config['table']
              .  " WHERE " . $this->_config['handle_col']
              .  " = :handle";
        
        // build the placeholder data
        $data = array(
            'handle' => $handle,
        );
        
        // get the results (a column of rows)
        $result = $obj->select('col', $stmt, $data);
        
        // done!
        return $result;
    }
}
?>