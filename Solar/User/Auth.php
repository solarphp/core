<?php
/**
 * 
 * Class for checking user authentication credentials.
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
 * Class for checking user authentication credentials.
 * 
 * @category Solar
 * 
 * @package Solar_User
 * 
 */
class Solar_User_Auth extends Solar_Base {
    
    /**
     * 
     * User-supplied configuration values.
     * 
     * Keys are:
     * 
     * driver => (string) The driver class, e.g. 'Solar_User_Auth_File'.
     * 
     * config => (array) Config for the authentication driver.
     * 
     * expire => (int) Authentication lifetime in seconds; zero is
     * forever.  Default is 14400 (4 hours).
     * 
     * idle => (int) Maximum allowed idle time in seconds; zero is
     * forever.  Default is 1800 (30 minutes).
     * 
     * allow => (bool) Whether or not to allow automatic login/logout.
     * 
     * post_handle => (string) Username key in $_POST array.
     * 
     * post_passwd => (string) Password key in $_POST array.
     * 
     * post_submit => (string) Submission key in $_POST array,
     * e.g. 'submit'.
     * 
     * submit_login => (string) The submission-key value to indicate a
     * login attempt; default is the 'SUBMIT_LOGIN' locale key value.
     * 
     * submit_logout => (string) The submission-key value to indicate a
     * login attempt; default is the 'SUBMIT_LOGOUT' locale key value.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'locale'        => 'Solar/User/Locale/',
        'driver'        => 'Solar_User_Auth_None',
        'config'        => null,
        'expire'        => 14400,
        'idle'          => 1800,
        'allow'         => true,
        'post_handle'   => 'handle',
        'post_passwd'   => 'passwd',
        'post_submit'   => 'submit',
        'submit_login'  => null,
        'submit_logout' => null,
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
     * Whether or not to allow authentication actions (login/logout).
     * 
     * @var bool
     * 
     */
    public $allow = true;
    
    /**
     * 
     * Convenience reference to $_SESSION['Solar_User_Auth']['active'].
     * 
     * This is the Unix time at which the authenticated handle was last
     * valid().
     * 
     * @var int
     * 
     * @see valid()
     * 
     */
    public $active;
    
    /**
     * 
     * Convenience reference to $_SESSION['Solar_User_Auth']['initial'].
     * 
     * This is the Unix time at which the handle was initially
     * authenticated.
     * 
     * @var int
     * 
     */
    public $initial;
    
    /**
     * 
     * Convenience reference to $_SESSION['Solar_User_Auth']['status'].
     * 
     * This is the status code of the current user authentication; the string
     * codes are:
     * 
     * ANON => The user is anonymous/unauthenticated (no attempt to 
     * authenticate)
     * 
     * EXPIRED => The max time for authentication has expired
     * 
     * IDLED => The authenticated user has been idle for too long
     * 
     * VALID => The user is authenticated and has not timed out
     * 
     * WRONG => The user attempted authentication but failed
     * 
     * @var string
     * 
     */
    public $status;
    
    /**
     * 
     * Convenience reference to $_SESSION['Solar_User_Auth']['handle'].
     * 
     * This is the currently authenticated handle.
     * 
     * @var string
     * 
     */
    public $handle;
    
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config An array of user-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        $this->_config['submit_login']  = $this->locale('SUBMIT_LOGIN');
        $this->_config['submit_logout'] = $this->locale('SUBMIT_LOGOUT');
        parent::__construct($config);
    }
    
    
    /**
     * 
     * Start a session with authentication.
     * 
     * @return void
     * 
     */
    public function start()
    {
        // Start the session; suppress errors if already started.
        // Technically this should have happened as part of the
        // Solar::start() process.
        @session_start();
        
        // initialize the session array if it does not exist
        if (! isset($_SESSION['Solar_User_Auth']) ||
            ! is_array($_SESSION['Solar_User_Auth'])) {
            
            $_SESSION['Solar_User_Auth'] = array(
                'status' => 'ANON',
                'handle'    => null,
                'initial'  => null,
                'active' => null
            );
        }
        
        // add convenience references to the session array keys
        $this->status  =& $_SESSION['Solar_User_Auth']['status'];
        $this->handle  =& $_SESSION['Solar_User_Auth']['handle'];
        $this->initial =& $_SESSION['Solar_User_Auth']['initial'];
        $this->active  =& $_SESSION['Solar_User_Auth']['active'];
        
        // instantiate a driver object. we do this here instead of in
        // the constructor so that custom drivers will find the session
        // already available to them (e.g. single sign-on systems and
        // HTTP-based systems).
        $this->_driver = Solar::factory(
            $this->_config['driver'],
            $this->_config['config']
        );
        
        // update any current authentication (including idle and expire).
        $this->valid();
        
        // are we allowing authentication actions?
        if ($this->allow) {
        
            // get the submit value and credentials
            $handle = Solar::post($this->_config['post_handle']);
            $passwd = Solar::post($this->_config['post_passwd']);
            $submit = Solar::post($this->_config['post_submit']);
            
            // check for a login request.
            if ($submit == $this->_config['submit_login']) {
                
                // check the storage driver to see if the handle
                // and passwd credentials are valid.
                $result = $this->_driver->valid($handle, $passwd);
                
                // were the credentials valid? (check if exactly boolean
                // true, as it may have returned a Solar error).
                if ($result === true) {
                    // login attempt succeeded.
                    $this->status  = 'VALID';
                    $this->handle  = $handle;
                    $this->initial = time();
                    $this->active  = time();
                    // flash forward the status text
                    $this->setFlash('status_text', $this->locale($this->status));
                } else {
                    // login attempt failed.
                    $this->reset('WRONG');
                }
            }
            
            // check for a logout request.
            if ($submit == $this->_config['submit_logout']) {
                // reset the authentication data
                $this->reset('LOGOUT');
            }
        }
    }
    
    /**
     * 
     * Revalidates any current authentication and updates idle time.
     * 
     * Note that if your script runs more than 1 second, it is possible
     * that multiple calls to valid() may result in the authentication
     * expiring in the middle of the script.  As such, if you only need
     * to check that the user is logged in, look at the value of
     * $this->status.
     * 
     * @return boolean Whether or not authentication is still valid.
     * 
     */
    public function valid()
    {
        // is the current user already authenticated?
        $valid = false;
        if ($this->status == 'VALID') {
            
            // Check if session authentication has expired
            $tmp = $this->initial + $this->_config['expire'];
            if ($this->_config['expire'] > 0 && $tmp < time()) {
                // past the expiration time
                $this->reset('EXPIRED');
                return false;
            }
    
            // Check if session has been idle for too long
            $tmp = $this->active + $this->_config['idle'];
            if ($this->_config['idle'] > 0 && $tmp < time()) {
                // past the idle time
                $this->reset('IDLED');
                return false;
            }
            
            // not expired, not idled, so update the active time
            $this->active = time();
            return true;
            
        }
        
        // flash forward the status text, and return
        return $valid;
    }
    
    /**
     * 
     * Resets any authentication data in the session.
     * 
     * Typically used for idling, expiration, and logout.
     *
     * @param string $status A Solar_User_Auth status string;
     * default is 'ANON'.
     * 
     * @return void
     * 
     */
    public function reset($status = 'ANON')
    {
        $status = strtoupper($status);
        $this->status = $status;
        $this->handle = null;
        $this->initial = null;
        $this->active = null;
        
        // flash forward any messages
        $this->setFlash('status_text', $this->locale($this->status));
    }
    
    
    /**
     * 
     * Sets a "read-once" session value for this class and a key.
     * 
     * @param string $key The specific type of information for the class.
     * 
     * @param mixed $val The value for the key; previous values will
     * be overwritten.
     * 
     * @return void
     * 
     */
    public function setFlash($key, $val)
    {
        Solar::setFlash(get_class($this), $key, $val);
    }
    
    /**
     * 
     * Appends a "read-once" session value for this class and key.
     * 
     * @param string $key The specific type of information for the class.
     * 
     * @param mixed $val The flash value to add to the key; this will
     * result in the flash becoming an array.
     * 
     * @return void
     * 
     */
    public function addFlash($key, $val)
    {
        Solar::addFlash(get_class($this), $key, $val);
    }
    
    /**
     * 
     * Retrieves a "read-once" session value, thereby removing the value.
     * 
     * @param string $class The related class for the flash.
     * 
     * @param string $key The specific type of information for the class.
     * 
     * @param mixed $val If the class and key do not exist, return
     * this value.  Default null.
     * 
     * @return mixed The "read-once" value.
     * 
     */
    public function getFlash($key, $val = null)
    {
        return Solar::getFlash(get_class($this), $key, $val);
    }
    
}
?>