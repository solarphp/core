<?php
/**
 * 
 * Class for checking user authentication credentials.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
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
 * Class for checking user authentication credentials.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 */
class Solar_Auth extends Solar_Base {
    
    /**
     * 
     * User-supplied configuration values.
     * 
     * Keys are:
     * 
     * : \\adapter\\ : (string) The adapter class, e.g. 'Solar_Auth_Adapter_File'.
     * 
     * : \\config\\ : (array) Config for the authentication adapter.
     * 
     * : \\expire\\ : (int) Authentication lifetime in seconds; zero is
     *   forever.  Default is 14400 (4 hours).
     * 
     * : \\idle\\ : (int) Maximum allowed idle time in seconds; zero is
     *   forever.  Default is 1800 (30 minutes).
     * 
     * : \\allow\\ : (bool) Whether or not to allow automatic login/logout.
     * 
     * : \\source\\ : (string) The source for auth credentials, 'get'
     *   (for Solar::get() method) or 'post' (for Solar::post() method).
     *   Default is 'post'.
     * 
     * : \\source_handle\\ : (string) Username key in the credential array source,
     *   default 'handle'.
     * 
     * : \\source_passwd\\ : (string) Password key in the credential array source,
     *   default 'passwd'.
     * 
     * : \\source_submit\\ : (string) Submission key in the credential array source,
     *   default 'submit'.
     * 
     * : \\submit_login\\ : (string) The submission-key value to indicate a
     *   login attempt; default is the 'SUBMIT_LOGIN' locale key value.
     * 
     * : \\submit_logout\\ : (string) The submission-key value to indicate a
     *   login attempt; default is the 'SUBMIT_LOGOUT' locale key value.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'adapter'        => 'Solar_Auth_Adapter_None',
        'config'        => null,
        'expire'        => 14400,
        'idle'          => 1800,
        'allow'         => true,
        'source'        => 'post',
        'source_handle' => 'handle',
        'source_passwd' => 'passwd',
        'source_submit' => 'submit',
        'submit_login'  => null,
        'submit_logout' => null,
    );
    
    /**
     * 
     * An adapter object instance.
     * 
     * @var object
     * 
     */
    protected $_adapter = null;
    
    /**
     * 
     * Flash-messaging object.
     * 
     * @var Solar_Flash
     * 
     */
    protected $_flash;
    
    /**
     * 
     * The source of auth credentials, either 'get' or 'post'.
     * 
     * @var string
     * 
     */
    protected $_source;
    
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
     * Convenience reference to $_SESSION['Solar_Auth']['active'].
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
     * Convenience reference to $_SESSION['Solar_Auth']['initial'].
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
     * Convenience reference to $_SESSION['Solar_Auth']['status'].
     * 
     * This is the status code of the current user authentication; the string
     * codes are:
     * 
     * : \\ANON\\ : The user is anonymous/unauthenticated (no attempt to 
     * authenticate)
     * 
     * : \\EXPIRED\\ : The max time for authentication has expired
     * 
     * : \\IDLED\\ : The authenticated user has been idle for too long
     * 
     * : \\VALID\\ : The user is authenticated and has not timed out
     * 
     * : \\WRONG\\ : The user attempted authentication but failed
     * 
     * @var string
     * 
     */
    public $status;
    
    /**
     * 
     * Convenience reference to $_SESSION['Solar_Auth']['handle'].
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
        
        // make sure the source is either 'get' or 'post'.
        $this->_source = strtolower($this->_config['source']);
        if ($this->_source != 'get' && $this->_source != 'post') {
            // default to post
            $this->_source = 'post';
        }
        
        // create the flash object
        $this->_flash = Solar::factory(
            'Solar_Flash',
            array('class' => get_class($this))
        );
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
        // start the session if one hasn't been started already
        if (session_id() === '') {
            session_start();
        }
        
        // initialize the session array if it does not exist
        if (! isset($_SESSION['Solar_Auth']) ||
            ! is_array($_SESSION['Solar_Auth'])) {
            
            $_SESSION['Solar_Auth'] = array(
                'status'  => 'ANON',
                'handle'  => null,
                'initial' => null,
                'active'  => null,
            );
        }
        
        // add convenience references to the session array keys
        $this->status  =& $_SESSION['Solar_Auth']['status'];
        $this->handle  =& $_SESSION['Solar_Auth']['handle'];
        $this->initial =& $_SESSION['Solar_Auth']['initial'];
        $this->active  =& $_SESSION['Solar_Auth']['active'];
        
        // instantiate an adapter object. we do this here instead of in
        // the constructor so that custom adapters will find the session
        // already available to them (e.g. single sign-on systems and
        // HTTP-based systems).
        $this->_adapter = Solar::factory(
            $this->_config['adapter'],
            $this->_config['config']
        );
        
        // update any current authentication (including idle and expire).
        $this->isValid();
        
        // are we allowing authentication actions?
        if ($this->allow) {
        
            // get the submit value
            $method = strtolower($this->_config['source']);
            $submit = Solar::$method($this->_config['source_submit']);
            
            // check for a login request.
            if ($submit == $this->_config['submit_login']) {
                
                // check the storage adapter to see if the handle
                // and passwd credentials are valid.
                $handle = Solar::post($this->_config['source_handle']);
                $passwd = Solar::post($this->_config['source_passwd']);
                $result = $this->_adapter->isValid($handle, $passwd);
                if ($result === true) {
                    $this->reset('VALID', $handle);
                } else {
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
     * @return bool Whether or not authentication is still valid.
     * 
     */
    public function isValid()
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
     * Typically used for idling, expiration, and logout.  Calls
     * [[php session_regenerate_id()]] to clear previous session.
     * 
     * @param string $status A Solar_Auth status string;
     * default is 'ANON'.
     * 
     * @param string $handle The authenticated user handle; only
     * honored if $status is 'VALID'.
     * 
     * @return void
     * 
     */
    public function reset($status = 'ANON', $handle = null)
    {
        $status = strtoupper($status);
        $this->status  = $status;
        $this->handle  = null;
        $this->initial = null;
        $this->active  = null;
        
        if ($status == 'VALID') {
            $now = time();
            $this->initial = $now;
            $this->active  = $now;
            $this->handle  = $handle;
        }
        
        // reset the session id and delete previous session
        session_regenerate_id(true);
        
        // flash forward any messages
        $this->_flash->set('status_text', $this->locale($this->status));
    }
    
    /**
     * 
     * Retrieves a "read-once" session value fopr Solar_Auth.
     * 
     * @param string $key The specific type of information.
     * 
     * @param mixed $val If the class and key do not exist, return
     * this value.  Default null.
     * 
     * @return mixed The "read-once" value.
     * 
     */
    public function getFlash($key, $val = null)
    {
        return $this->_flash->get($key, $val);
    }
}
?>