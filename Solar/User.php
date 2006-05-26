<?php
/**
 * 
 * Meta-container for the current user to hold auth and roles.
 * 
 * When prefs and permissions come along, will hold those too.
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
 * 
 * Meta-container for the current user to hold auth and roles.
 * 
 * When prefs and permissions come along, will hold those too.
 * 
 * @category Solar
 * 
 * @package Solar_User
 * 
 */
class Solar_User extends Solar_Base {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'auth' => null,
        'role' => null,
        'access' => null
    );
    
    /**
     * 
     * User authentication object.
     * 
     * @var object
     * 
     */
    public $auth;
    
    /**
     * 
     * User roles (group membership) object.
     * 
     * @var object
     * 
     */
    public $role;
    
    /**
     * 
     * Authorized access object.
     * 
     * @var object
     * 
     */
    public $access;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     * @return void
     * 
     */
    public function __construct($config = null)
    {
        // construction
        parent::__construct($config);
        
        // set up an authentication object.
        $this->auth = Solar::dependency('Solar_Auth', $this->_config['auth']);
        
        // set up the roles object.
        $this->role = Solar::dependency('Solar_Role', $this->_config['role']);
        
        // set up the access object.
        $this->access = Solar::dependency('Solar_Access', $this->_config['access']);
        
        // start up authentication
        $this->auth->start();
        
        // is this a valid authenticated user?
        if ($this->auth->status == 'VALID') {
            // yes, the user is authenticated as valid.
            // load up any roles for the user.
            $this->role->load($this->auth->handle);
        } else {
            // no, user is not valid.  
            // clear out any previous roles.
            $this->role->reset();
            $this->access->reset();
        }
        
        // load up the access list for the handle and roles
        $this->access->load($this->auth->handle, $this->role->list);
    }
}
?>