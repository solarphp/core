<?php
/**
 * 
 * Class for reading access privileges from a database table.
 * 
 * @category Solar
 * 
 * @package Solar_Access
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */

/**
 * 
 * Class for reading access privileges from a database table.
 * 
 *     0:flag 1:type 2:name 3:class 4:action
 * 
 * @category Solar
 * 
 * @package Solar_Access
 * 
 */
class Solar_Access_Adapter_Sql extends Solar_Access_Adapter
{
    /**
     * 
     * Config keys
     *
     * `sql`
     * : (string|array) How to get the SQL object.  If a string, is
     *   treated as a [[Solar_Registry::get()]] object name.  If array, treated as
     *   config for a standalone Solar_Sql object.
     *
     * `table`
     * : (string) Name of the table holding access data.
     * 
     * `flag_col`
     * : (string) Name of the column with privilege flag (the stored value in
     *   the column should be like a boolean, such as allow/deny, t/f, T/F,
     *   y/n, Y/N, or 0/1).
     * 
     * `type_col`
     * : (string) Name of the column with access type info ('handle' or
     *   'role').
     * 
     * `name_col`
     * : (string) Name of the column with the handle or role name.
     * 
     * `class_col`
     * : (string) Name of the column with the class name.
     * 
     * `action_col`
     * : (string) Name of the column with the action name.
     * 
     * `process_col`
     * : (string) Name of the column with the submit key name.
     * 
     * @var array
     * 
     */
    protected $_Solar_Access_Adapter_Sql = array(
        'sql'         => 'sql',
        'table'       => 'acl',
        'flag_col'    => 'flag',
        'type_col'    => 'type',
        'name_col'    => 'name',
        'class_col'   => 'class_name',
        'action_col'  => 'action_name',
        'order_col'   => 'position',
    );
    
    /**
     * 
     * Fetches access privileges for a user handle and roles.
     * 
     * Uses a SELECT similar to the following:
     * 
     * {{code: sql
     *     SELECT $cols
     *     FROM $table
     *     WHERE (type = 'handle' AND name IN ($handle_list))
     *     OR (type = 'role' AND name IN ($role_list))
     *     OR (type = 'owner')
     *     ORDER BY $order
     * }}
     * 
     * @param string $handle User handle.
     * 
     * @param array $roles User roles.
     * 
     * @return array
     * 
     */
    public function fetch($handle, $roles)
    {
        /**
         * prepare query elements
         */
        
        // columns to select
        $cols = array(
            $this->_config['flag_col']    . ' AS allow',
            $this->_config['type_col']    . ' AS type',
            $this->_config['name_col']    . ' AS name',
            $this->_config['class_col']   . ' AS class',
            $this->_config['action_col']  . ' AS action',
        );
        
        // the "handle" condition
        // `(type = 'handle' AND name IN (...))`
        $handle_cond = "({$this->_config['type_col']} = :handle_type"
                     . " AND {$this->_config['name_col']} IN (:handle_list))";
        
        // the handle list
        if ($handle) {
            // user is authenticated
            $handle_list = array($handle, '*', '+');
        } else {
            // user is anonymous
            $handle_list = array('*');
        }
        
        // the "role" condition
        // `(type = 'role' AND name IN (...))`
        $role_cond = "({$this->_config['type_col']} = :role_type"
                   . " AND {$this->_config['name_col']} IN (:role_list))";
        
        // the role list
        $role_list = (array) $roles;
        $role_list[] = '*';
        
        // the "owner" condition
        // `type = 'owner'`
        $owner_cond = "({$this->_config['type_col']} = :owner_type)";
        
        // collect data to bind into the query
        $data = array(
            'handle_type' => 'handle',
            'handle_list' => $handle_list,
            'role_type'   => 'role',
            'role_list'   => $role_list,
            'owner_type'  => 'owner',
        );
        
        /**
         * build and execute the query
         */
        
        // get the dependency object of class Solar_Sql
        $sql = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // get a selection tool using the dependency object
        $select = Solar::factory( 'Solar_Sql_Select', array(
            'sql' => $sql
        ));
        
        // build the select
        $select->from($this->_config['table'], $cols)
               ->where($handle_cond)
               ->orWhere($role_cond)
               ->orWhere($owner_cond)
               ->order($this->_config['order_col'])
               ->bind($data);
        
        // fetch the access list
        $access = $select->fetchAll();
        
        // set 'allow' flag to boolean on each access item
        $allow = array('allow', 't', 'T', 'y', 'Y', '1');
        foreach ($access as $key => $val) {
            $access[$key]['allow'] = (bool) in_array($val['allow'], $allow);
        }
        
        // return access list
        return $access;
    }
    
    public function isOwner($content)
    {
        return true;
    }
}
