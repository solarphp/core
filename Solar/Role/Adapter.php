<?php
/**
 * 
 * Abstract role adapter.
 * 
 * @category Solar
 * 
 * @package Solar_Role
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
abstract class Solar_Role_Adapter extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * @var array
     * 
     */
    protected $_Solar_Role_Adapter = array(
        'session_class' => 'Solar_Role_Adapter',
    );
    
    /**
     * 
     * A class-segmented session-variable reference.
     * 
     * @var Solar_Session
     * 
     */
    protected $_session;
    
    /**
     * 
     * Constructor to set up the storage adapter.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // basic config option settings
        parent::__construct($config);
        
        // make sure we have a session class name; this determines how the
        // session store is segmented.  when you have multiple adapters that
        // need to use the same store, this is useful.
        if (! $this->_config['session_class']) {
            $this->_config['session_class'] = 'Solar_Role_Adapter';
        }
        
        // get a session segment
        $this->_session = Solar::factory(
            'Solar_Session',
            array('class' => $this->_config['session_class'])
        );
    }
    
    /**
     * 
     * Provides magic "isRoleName()" to map to "is('role_name')".
     * 
     * @param string $method The called method name.
     * 
     * @param array $params Parameters passed to the method.
     * 
     * @return bool
     * 
     */
    public function __call($method, $params)
    {
        if (substr($method, 0, 2) == 'is') {
            // convert from isRoleName to role_name
            $role = substr($method, 2);
            $role = preg_replace('/([a-z])([A-Z])/', '$1_$2', $role);
            $role = strtolower($role);
            // call is() on the role name
            return $this->is($role);
        } else {
            throw $this->_exception('ERR_METHOD_NOT_IMPLEMENTED', array(
                'method' => $method,
                'params' => $params,
            ));
        }
    }
    
    /**
     * 
     * Load the list of roles for the given user from the adapter.
     * 
     * @param string $handle The username to load roles for.
     * 
     * @return void
     * 
     */
    public function load($handle)
    {
        // fetch the role list using the adapter-specific method
        $result = $this->fetch($handle);
        if ($result) {
            $this->setList($result);
        }
    }
    
    /**
     * 
     * Gets the list of all loaded roles for the user.
     * 
     * @return array
     * 
     */
    public function getList()
    {
        return $this->_session->get('list', array());
    }
    
    /**
     * 
     * Sets the list, overriding what is there already.
     * 
     * @param array $list The list of roles to set.
     * 
     * @return void
     * 
     */
    public function setList($list)
    {
        $this->_session->set('list', (array) $list);
    }
    
    /**
     * 
     * Appends a list of roles to the existing list of roles.
     * 
     * @param array $list The list of roles to append.
     * 
     * @return void
     * 
     */
    public function addList($list)
    {
        settype($list, 'array');
        foreach ($list as $val) {
            $this->_session->add('list', $val);
        }
    }
    
    /**
     * 
     * Appends a single role to the existing list of roles.
     * 
     * @param string $list The role to append.
     * 
     * @return void
     * 
     */
    public function add($val)
    {
        $this->_session->add('list', (string) $val);
    }
    
    /**
     * 
     * Resets the role list to nothing.
     * 
     * @return void
     * 
     */
    public function reset()
    {
        $this->setList(array());
    }
    
    /**
     * 
     * Check to see if a user is in a role.
     * 
     * @param string $role The role to check.
     * 
     * @return bool True if the user is in the role, or false if not.
     * 
     */
    public function is($role = null)
    {
        return in_array($role, $this->getList());
    }
    
    /**
     * 
     * Check to see if a user is in any of the listed roles.
     * 
     * @param string|array $roles The role(s) to check.
     * 
     * @return bool True if the user is in any of the listed roles (a
     * logical 'or'), false if not.
     * 
     */
    public function isAny($roles = array())
    {
        // loop through all of the roles, returning 'true' the first
        // time we find a matching role.
        $list = $this->getList();
        foreach ((array) $roles as $role) {
            if (in_array($role, $list)) {
                return true;
            }
        }
        
        // we got through the whole array without finding a match.
        // therefore, user was not in any of the roles.
        return false;
    }
    
    /**
     * 
     * Check to see if a user is in all of the listed roles.
     * 
     * @param string|array $roles The role(s) to check.
     * 
     * @return bool True if the user is in all of the listed roles (a
     * logical 'and'), false if not.
     * 
     */
    public function isAll($roles = array())
    {
        // loop through all of the roles, returning 'false' the first
        // time we find the user is not in one of the roles.
        $list = $this->getList();
        foreach ((array) $roles as $role) {
            if (! in_array($role, $list)) {
                return false;
            }
        }
        
        // we got through the whole list; therefore, the user is in all
        // of the noted roles.
        return true;
    }
    
    /**
     * 
     * Adapter-specific method to find roles for loading.
     * 
     * @param string $handle User handle to get roles for.
     * 
     * @return array An array of discovered roles.
     * 
     */
    abstract public function fetch($handle);
}
