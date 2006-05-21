<?php

class Solar_User_Access extends Solar_Base {
    
    /**
     * 
     * User-supplied configuration values.
     * 
     * @var array
     */
    protected $_config = array(
        'locale' => 'Solar/User/Locale/',
        'driver' => 'Solar_User_Access_Open',
        'config' => null,
    );
    
    /**
     * 
     * A driver object instance.
     * 
     * @var object
     * 
     */
    protected $_driver = null;
    
    /**
     * 
     * The access list for a handle and roles.
     * 
     * @var array
     * 
     */
    public $list = array();
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_driver = Solar::factory(
            $this->_config['driver'],
            $this->_config['config']
        );
    }
    
    /**
     * 
     * Fetches the access list from the driver into $this->list.
     * 
     * @param string $handle The username handle to fetch access
     * controls for.
     * 
     * @param array $roles The user roles to fetch access controls for.
     * 
     * @return void
     * 
     */
    public function fetch($handle, $roles)
    {
        // reverse so that last ones are checked first
        $this->list = array_reverse($this->_driver->fetch($handle, $roles));
    }
    
    /**
     * 
     * Tells whether or not to allow access to a class/action/submit combination.
     * 
     * @param string $class The class name of the control; use '*' for
     * all classes.
     * 
     * @param string $action The action within that class; use '*' for
     * all actions.
     * 
     * @param string $submit The submission value within the action; use
     * '*' for all submissions.
     * 
     * @return bool True if the current handle or role is allowed 
     * access, false if not.
     * 
     */
    public function allow($class = '*', $action = '*', $submit = '*')
    {
        foreach ($this->list as $info) {
            $class_match  = ($info['class']  == $class  || $info['class'] == '*');
            $action_match = ($info['action'] == $action || $info['action'] == '*');
            $submit_match = ($info['submit'] == $submit || $info['submit'] == '*');
            if ($class_match && $action_match && $submit_match) {
                // all params match, return the flag (true or false)
                return (bool) $info['allow'];
            }
        }
        // no matching params, deny by default
        return false;
    }
    
    /**
     * 
     * Resets the current access controls to a blank array.
     * 
     * @return void
     * 
     */
    public function reset()
    {
        $this->list = array();
    }
}
?>