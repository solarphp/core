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
 * @todo Add $this->uri (for OpenID stuff).
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
     * : \\config\\ : (array) Construction-time config keys to pass to the adapter
     *   to override Solar.config.php values.  Default is null.
     * 
     * : \\expire\\ : (int) Authentication lifetime in seconds; zero is
     *   forever.  Default is 14400 (4 hours).
     * 
     * : \\idle\\ : (int) Maximum allowed idle time in seconds; zero is
     *   forever.  Default is 1800 (30 minutes).
     * 
     * : \\allow\\ : (bool) Whether or not to allow login/logout attempts.
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
    protected $_Solar_Auth = array(
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
     * This is the currently authenticated user handle.
     * 
     * @var string
     * 
     */
    public $handle;
    
    /**
     * 
     * Convenience reference to $_SESSION['Solar_Auth']['email'].
     * 
     * This is the email address of the currently authenticated user. 
      * May or may not be populated by the adapter.
     * 
     * @var string
     * 
     */
    public $email;
    
    /**
     * 
     * Convenience reference to $_SESSION['Solar_Auth']['moniker'].
     * 
     * This is the "display name" or "full name" of the currently
     * authenticated user.  May or may not be populated by the adapter.
     * 
     * @var string
     * 
     */
    public $moniker;
    
    /**
     * 
     * Convenience reference to $_SESSION['Solar_Auth']['uri'].
     * 
     * This is the URI for the currently authenticated user.  May or
     * may not be populated by the adapter.
     * 
     * @var string
     * 
     */
    public $uri;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config An array of user-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        $this->_Solar_Auth['submit_login']  = $this->locale('SUBMIT_LOGIN');
        $this->_Solar_Auth['submit_logout'] = $this->locale('SUBMIT_LOGOUT');
        parent::__construct($config);
        
        // instantiate an adapter object. we do this here instead of in
        // the constructor so that custom adapters will find the session
        // already available to them (e.g. single sign-on systems and
        // HTTP-based systems).
        $this->_adapter = Solar::factory(
            $this->_config['adapter'],
            $this->_config['config']
        );
        
        // set the common source* and submit* config values to the
        // so non-SSO methods can look at the request properly.
        $common = array(
            'source'        => $this->_config['source'],
            'source_handle' => $this->_config['source_handle'],
            'source_passwd' => $this->_config['source_passwd'],
            'source_submit' => $this->_config['source_submit'],
            'submit_login'  => $this->_config['submit_login'],
            'submit_logout' => $this->_config['submit_logout'],
        );
        $this->_adapter->setCommon($common);
        
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
                'initial' => null,
                'active'  => null,
                'handle'  => null,
                'email'   => null,
                'moniker' => null,
                'uri'     => null,
            );
        }
        
        // add convenience references to the session array keys
        $this->status  =& $_SESSION['Solar_Auth']['status'];
        $this->initial =& $_SESSION['Solar_Auth']['initial'];
        $this->active  =& $_SESSION['Solar_Auth']['active'];
        $this->handle  =& $_SESSION['Solar_Auth']['handle'];
        $this->email   =& $_SESSION['Solar_Auth']['email'];
        $this->moniker =& $_SESSION['Solar_Auth']['moniker'];
        $this->uri     =& $_SESSION['Solar_Auth']['uri'];
        
        // update idle and expire times no matter what
        $this->updateIdleExpire();
        
        // if current auth is not valid, and processing is allowed,
        // process login attempts
        if (! $this->isValid() && $this->allow &&
            $this->_adapter->isLoginRequest()) {
                
            // check the login validity
            if ($this->_adapter->isLoginValid() === true) {
                $this->reset('VALID');
                $this->handle  = $this->_adapter->getHandle();
                $this->moniker = $this->_adapter->getMoniker();
                $this->email   = $this->_adapter->getEmail();
                $this->uri     = $this->_adapter->getUri();
            } else {
                $code = $this->_adapter->getErrCode();
                if ($err) {
                    // use adapter-specific error code
                    $this->reset($code);
                } else {
                    // generic error
                    $this->reset('WRONG');
                }
            }
            
        }
        
        // if current auth **is** valid, and processing is allowed,
        // process logout attempts.
        if ($this->isValid() && $this->allow &&
            $this->_adapter->isLogoutRequest()) {
            $this->reset();
        }
    }
    
    /**
     * 
     * Updates idle and expire times, invalidating authentication if
     * they are exceeded.
     * 
     * Note that if your script runs more than 1 second, it is possible
     * that multiple calls to this method may result in the authentication
     * expiring in the middle of the script.  As such, if you only need
     * to check that the user is logged in, call $this->isValid().
     * 
     * @return bool Whether or not authentication is still valid.
     * 
     */
    public function updateIdleExpire()
    {
        // is the current user already authenticated?
        if ($this->isValid()) {
            
            // Check if session authentication has expired
            $tmp = $this->initial + $this->_config['expire'];
            if ($this->_config['expire'] > 0 && $tmp < time()) {
                // past the expiration time
                // flash forward the status text, and return
                $this->reset('EXPIRED');
                return false;
            }
    
            // Check if session has been idle for too long
            $tmp = $this->active + $this->_config['idle'];
            if ($this->_config['idle'] > 0 && $tmp < time()) {
                // past the idle time
                // flash forward the status text, and return
                $this->reset('IDLED');
                return false;
            }
            
            // not expired, not idled, so update the active time
            $this->active = time();
            return true;
            
        }
        
        return false;
    }
    
    /**
     * 
     * Tells whether the current authentication is valid.
     * 
     * @return bool Whether or not authentication is still valid.
     * 
     */
    public function isValid()
    {
        return $this->status == 'VALID';
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
    public function reset($status = 'ANON')
    {
        $status = strtoupper($status);
        
        $this->status  = $status;
        $this->initial = null;
        $this->active  = null;
        $this->handle  = null;
        $this->moniker = null;
        $this->email   = null;
        
        if ($status == 'VALID') {
            // restart the timers
            $now = time();
            $this->initial = $now;
            $this->active  = $now;
        } else {
            // clear the adapter values too
            $this->_adapter->reset();
        }
        
        // reset the session id and delete previous session
        session_regenerate_id(true);
        
        // flash forward any messages
        $this->_flash->set('status_text', $this->locale($this->status));
    }
    
    /**
     * 
     * Retrieves a "read-once" session value for Solar_Auth.
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