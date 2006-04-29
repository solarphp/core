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
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_driver = Solar::factory(
            $this->_config['driver'],
            $this->_config['config']
        );
    }
    
    public function fetch($handle, $roles)
    {
        // reverse so that last ones are checked first
        $this->list = array_reverse($this->_driver->fetch($handle, $roles));
    }
    
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
    
    public function reset()
    {
        $this->list = array();
    }
}
?>