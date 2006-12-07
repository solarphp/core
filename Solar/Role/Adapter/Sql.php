<?php
/**
 * 
 * Adapter to fetch roles from an SQL database table.
 * 
 * @category Solar
 * 
 * @package Solar_User
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Abstract role adapter class.
 */
Solar::loadClass('Solar_Role_Adapter');

/**
 * 
 * Adapter to fetch roles from an SQL database table.
 * 
 * @category Solar
 * 
 * @package Solar_User
 * 
 */
class Solar_Role_Adapter_Sql extends Solar_Role_Adapter {
    
    /**
     * 
     * User-supplied configuration values.
     * 
     * Keys are ...
     * 
     * `sql`
     * : (dependency) A Solar_Sql dependency.
     * 
     * `table`
     * : (string) The table where roles are stored.
     * 
     * `handle_col`
     * : (string) The column for user handles.
     * 
     * `role_col`
     * : (string) The column for roles.
     * 
     * `where`
     * : (string|array) Additional _multiWhere() conditions to use
     *   when selecting role rows.
     * 
     * @var array
     * 
     */
    protected $_Solar_Role_Adapter_Sql = array(
        'sql'        => 'sql',
        'table'      => 'roles',
        'handle_col' => 'handle',
        'role_col'   => 'name',
        'where'      => array(),
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
        $sql = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // get a selection tool using the dependency object
        $select = Solar::factory(
            'Solar_Sql_Select',
            array('sql' => $sql)
        );
        
        // build the select
        $select->from($this->_config['table'], $this->_config['role_col'])
               ->where("{$this->_config['handle_col']} = ?", $handle)
               ->multiWhere($this->_config['where']);
        
        // get the results (a column of rows)
        $result = $select->fetch('col');
        
        // done!
        return $result;
    }
}
