<?php

/**
 * 
 * Class for checking user authentication credentials.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_User
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
 * @package Solar
 * 
 * @subpackage Solar_User
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
     * post_username => (string) Password key in $_POST array.
     * 
     * post_password => (string) Username key in $_POST array.
     * 
     * action_login => (string) The action-key value to indicate a
     * login attempt.
     * 
     * action_logout => (string) The action-key value to indicate a
     * logout attempt.
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    
    protected $_config = array(
        'locale'        => 'Solar/User/Locale/',
        'class'         => 'Solar_User_Auth_None',
        'options'       => null,
        'expire'        => 14400,
        'idle'          => 1800,
        'allow'         => true,
        'post_op'       => 'op',
        'post_password' => 'password',
        'post_username' => 'username',
        'op_login'      => 'login',
        'op_logout'     => 'logout',
    );
    
    
    /**
     * 
     * A driver object instance.
     * 
     * @access protected
     * 
     * @var object
     * 
     */
    
    protected $_driver = null;
    
    
    /**
     * 
     * Whether or not to allow authentication actions (login/logout).
     * 
     * @access private
     * 
     * @var bool
     * 
     */
    
    public $allow = true;
    
    
    /**
     * 
     * Convenience reference to $_SESSION['Solar_User_Auth']['last_active'].
     * 
     * This is the Unix time at which the authenticated username was last
     * valid().
     * 
     * @access public
     * 
     * @var int
     * 
     * @see valid()
     * 
     */
    
    public $last_active;
    
    
    /**
     * 
     * Convenience reference to $_SESSION['Solar_User_Auth']['login_time'].
     * 
     * This is the Unix time at which the username was authenticated.
     * 
     * @access public
     * 
     * @var int
     * 
     */
    
    public $login_time;
    
    
    /**
     * 
     * Convenience reference to $_SESSION['Solar_User_Auth']['status_code'].
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
     * @access public
     * 
     * @var int
     * 
     */
    
    public $status_code;
    
    
    /**
     * 
     * Convenience reference to $_SESSION['Solar_User_Auth']['status_text'].
     * 
     * This is message text related to the status code of the current
     * authentication.
     * 
     * @access public
     * 
     * @var int
     * 
     */
    
    public $status_text;
    
    
    /**
     * 
     * Convenience reference to $_SESSION['Solar_User_Auth']['username'].
     * 
     * This is the currently authenticated username.
     * 
     * @access public
     * 
     * @var string
     * 
     */
    
    public $username;
    
    
    // ----------------------------------------------------------------
    // 
    // Public methods.
    // 
    // ----------------------------------------------------------------
    
    
    /**
     * 
     * Start a session with authentication.
     * 
     * @access public
     * 
     * @return void
     * 
     */
    
    public function start()
    {
        // Start the session; suppress errors if already started.
        @session_start();
        
        // initialize the session array if it does not exist
        if (! isset($_SESSION['Solar_User_Auth']) ||
            ! is_array($_SESSION['Solar_User_Auth'])) {
            
            $_SESSION['Solar_User_Auth'] = array(
                'status_code' => 'ANON',
                'status_text' => $this->locale('ANON'),
                'username'    => null,
                'login_time'  => null,
                'last_active' => null
            );
        }
        
        // add convenience references to the session array keys
        $this->status_code =& $_SESSION['Solar_User_Auth']['status_code'];
        $this->status_text =& $_SESSION['Solar_User_Auth']['status_text'];
        $this->username    =& $_SESSION['Solar_User_Auth']['username'];
        $this->login_time  =& $_SESSION['Solar_User_Auth']['login_time'];
        $this->last_active =& $_SESSION['Solar_User_Auth']['last_active'];
        
        // instantiate a driver object. we do this here instead of in
        // the constructor so that custom drivers will find the session
        // already available to them (e.g. single sign-on systems and
        // HTTP-based systems).
        $this->_driver = Solar::object(
            $this->_config['class'],
            $this->_config['options']
        );
        
        // update any current authentication (including idle and expire).
        $this->valid();
        
        // are we allowing authentication actions?
        if ($this->allow) {
        
            // get the action and credentials
            $action   = Solar::post($this->_config['post_op']);
            $username = Solar::post($this->_config['post_username']);
            $password = Solar::post($this->_config['post_password']);
            
            // check for a login request.
            if ($action == $this->_config['op_login']) {
                
                // check the storage driver to see if the username
                // and password credentials are valid.
                $result = $this->_driver->valid($username, $password);
                
                // were the credentials valid? (check if exactly boolean
                // true, as it may have returned a Solar error).
                if ($result === true) {
                    // login attempt succeeded.
                    $this->status_code = 'VALID';
                    $this->status_text = $this->locale('VALID');
                    $this->username    = $username;
                    $this->login_time  = time();
                    $this->last_active = time();
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
     * $this->status_code.
     * 
     * @access public
     * 
     * @return boolean Whether or not authentication is still valid.
     * 
     */
    
    public function valid()
    {
        // is the current user already authenticated?
        if ($this->status_code == 'VALID') {
            
            // Check if session authentication has expired
            $tmp = $this->login_time + $this->_config['expire'];
            if ($this->_config['expire'] > 0 && $tmp < time()) {
                // past the expiration time
                $this->reset('EXPIRED');
                return false;
            }
    
            // Check if session has been idle for too long
            $tmp = $this->last_active + $this->_config['idle'];
            if ($this->_config['idle'] > 0 && $tmp < time()) {
                // past the idle time
                $this->reset('IDLED');
                return false;
            }
            
            // not expired, not idled, so update the last_active time
            $this->last_active = time();
            return true;
            
        } else {
            // current user is not already authenticated.
            return false;
        }
    }
    
    
    /**
     * 
     * Resets any authentication data in the session.
     * 
     * Typically used for idling, expiration, and logout.
     *
     * @access public
     * 
     * @param string $status_code A Solar_User_Auth status string;
     * default is 'ANON'.
     * 
     * @return void
     * 
     */
    
    public function reset($status_code = 'ANON')
    {
        $status_code = strtoupper($status_code);
        $this->status_code = $status_code;
        $this->status_text = $this->locale($status_code);
        $this->username = null;
        $this->login_time = null;
        $this->last_active = null;
    }
}
?>