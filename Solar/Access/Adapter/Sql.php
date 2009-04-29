<?php
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
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
     * `order_col`
     * : (string) Order the results by this column.
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
         * prepare handle and role lists
         */
        // the handle list
        if ($handle) {
            // user is authenticated
            $handle_list = array($handle, '*', '+');
        } else {
            // user is anonymous
            $handle_list = array('*');
        }
        
        // the role list
        $role_list = (array) $roles;
        $role_list[] = '*';
        
        /**
         * prepare the sql object
         */
        // get the dependency object of class Solar_Sql
        $sql = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // get a selection tool using the dependency object
        $select = Solar::factory( 'Solar_Sql_Select', array(
            'sql' => $sql
        ));
        
        // select these columns from the table
        $cols = array(
            $this->_config['flag_col']    . ' AS allow',
            $this->_config['type_col']    . ' AS type',
            $this->_config['name_col']    . ' AS name',
            $this->_config['class_col']   . ' AS class',
            $this->_config['action_col']  . ' AS action',
        );
        
        $select->from($this->_config['table'], $cols);
        
        /**
         * add major conditions
         */
        
        // pretty vars
        $type_col = $this->_config['type_col'];
        $name_col = $this->_config['name_col'];
        
        // `WHERE (type = 'handle' AND name IN (...))`
        $select->where("($type_col = ?", 'handle');
        $select->where("$name_col IN (?))", $handle_list);
        
        // `OR (type = 'role' AND name IN (...))`
        $select->orWhere("($type_col = ?", 'role');
        $select->where("$name_col IN (?))", $role_list);
        
        // `OR (type = 'owner')`
        $select->orWhere("($type_col = ?)", 'owner');
        
        // order the columns
        $select->order($this->_config['order_col']);
        
        /**
         * fetch, process, and return
         */
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
    
    /**
     * 
     * Checks to see if the current user is the owner of application-specific
     * content; always returns true, to allow for programmatic owner checks.
     * 
     * @param mixed $content The content to check ownership of.
     * 
     * @return bool
     * 
     */
    public function isOwner($content)
    {
        return true;
    }
}
