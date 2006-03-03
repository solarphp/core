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
     * class => (string) The driver class, e.g. 'Solar_User_Auth_File'.
     * 
     * options => (array) Options for the authentication driver.
     * 
     * expire => (int) Authentication lifetime in seconds; zero is
     * forever.  Default is 14400 (4 hours).
     * 
     * idle => (int) Maximum allowed idle time in seconds; zero is
     * forever.  Default is 1800 (30 minutes).
     * 
     * allow => (bool) Whether or not to allow automatic login/logout.
     * 
     * post_action => (string) Login/logout action key in $_POST array,
     * e.g. 'op'.
     * 
     * post_handle => (string) Username key in $_POST array.
     * 
     * post_passwd => (string) Password key in $_POST array.
     * 
     * action_login => (string) The action-key value to indicate a
     * login attempt.
     * 
     * action_logout => (string) The action-key value to indicate a
     * logout attempt.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'locale'      => 'Solar/User/Locale/',
        'class'       => 'Solar_User_Auth_None',
        'options'     => null,
        'expire'      => 14400,
        'idle'        => 1800,
        'allow'       => true,
        'post_op'     => 'op',
        'post_passwd' => 'username',
        'post_handle' => 'password',
        'op_login'    => 'login',
        'op_logout'   => 'logout',
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
     * This is the Unix time at which the handle was authenticated.
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
     * @var int
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
    
    
    // ----------------------------------------------------------------
    // 
    // Public methods.
    // 
    // ----------------------------------------------------------------
    
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
        $this->status =& $_SESSION['Solar_User_Auth']['status'];
        $this->handle    =& $_SESSION['Solar_User_Auth']['handle'];
        $this->initial  =& $_SESSION['Solar_User_Auth']['initial'];
        $this->active =& $_SESSION['Solar_User_Auth']['active'];
        
        // instantiate a driver object. we do this here instead of in
        // the constructor so that custom drivers will find the session
        // already available to them (e.g. single sign-on systems and
        // HTTP-based systems).
        $this->_driver = Solar::factory(
            $this->_config['class'],
            $this->_config['options']
        );
        
        // update any current authentication (including idle and expire).
        $this->valid();
        
        // are we allowing authentication actions?
        if ($this->allow) {
        
            // get the action and credentials
            $action   = Solar::post($this->_config['post_op']);
            $handle = Solar::post($this->_config['post_handle']);
            $passwd = Solar::post($this->_config['post_passwd']);
            
            // check for a login request.
            if ($action == $this->_config['op_login']) {
                
                // check the storage driver to see if the handle
                // and passwd credentials are valid.
                $result = $this->_driver->valid($handle, $passwd);
                
                // were the credentials valid? (check if exactly boolean
                // true, as it may have returned a Solar error).
                if ($result === true) {
                    // login attempt succeeded.
                    $this->status = 'VALID';
                    $this->handle    = $handle;
                    $this->initial  = time();
                    $this->active = time();
                    // flash forward the status text
                    $this->setFlash('status_text', $this->locale($this->status));
                } else {
                    // login attempt failed.
                    $this->reset('WRONG');
                }
            }
            
            // check for a logout request.
            if ($action == $this->_config['op_logout']) {
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
}
?>