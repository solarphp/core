<?php
/**
 * 
 * Authentication class.
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
class Solar_Auth extends Solar_Base {
    
    /**
     * 
     * The user is anonymous/unauthenticated (no attempt has been made to 
     * authenticate).
     * 
     * @const string
     * 
     */
    const ANON = 'ANON';
    
    /**
     * 
     * The max time for authentication has expired.
     * 
     * @const string
     * 
     */
    const EXPIRED = 'EXPIRED';
    
    /**
     * 
     * The authenticated user has been idle for too long.
     * 
     * @const string
     * 
     */
    const IDLED = 'IDLED';
    
    /**
     * 
     * The user is authenticated and has not timed out.
     * 
     * @const string
     * 
     */
    const VALID = 'VALID';
    
    /**
     * 
     * The user attempted authentication but failed.
     * 
     * @const string
     * 
     */
    const WRONG = 'WRONG';
    
    /**
     * 
     * Default configuration values.
     * 
     * @config int expire Authentication lifetime in seconds; zero is
     *   forever.  Default is 14400 (4 hours). If this value is greater than
     *   the non-zero PHP ini setting for `session.cookie_lifetime`, it will
     *   throw an exception.
     * 
     * @config int idle Maximum allowed idle time in seconds; zero is
     *   forever.  Default is 1440 (24 minutes). If this value is greater than
     *   the the PHP ini setting for `session.gc_maxlifetime`, it will throw
     *   an exception.
     * 
     * @config bool auto_login Whether or not to allow automatic login at 
     * start() time. Default true.
     *
     * @config bool auto_logout Whether or not to allow automatic logout at 
     * start() time. Default true.
     * 
     * @config callback login_callback A callback to execute after successful
     * login, but before the source postLogin() method is called.
     * 
     * @config callback logout_callback A callback to execute after successful
     * logout, but before the source postLogout() method is called.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth = array(
        'expire'         => 14400,
        'idle'           => 1440,
        'auto_login'  => true,
        'auto_logout' => true,
        'login_callback'  => null,
        'logout_callback' => null,
        'login_protocol' => array(
            'adapter' => 'Solar_Auth_Login_Adapter_Post',
        ),
        'logout_protocol' => array(
            'adapter' => 'Solar_Auth_Logout_Adapter_Post',
        ),
        'storage' => array(
            'adapter' => 'Solar_Auth_Storage_Adapter_Var',
        ),
    );
    
    /**
     * 
     * A session object for retaining authentication state between requests
     * 
     * @var Solar_Session
     * 
     */
    protected $_session;
    
    /**
     * 
     * Magic "public" properties that are actually stored in the session.
     * 
     * The available magic properties are ...
     * 
     * - status:  (string)  The Unix time at which the authenticated handle was last 
     *   valid.
     * 
     * - initial:  (int)  The Unix time at which the handle was initially authenticated.
     * 
     * - active:  (int)  The status code of the current user authentication. The string
     *   codes are ...
     *   
     *     - Solar_Auth::ANON (or empty): The user is anonymous/unauthenticated (no attempt to authenticate)
     *     
     *     - Solar_Auth::EXPIRED: The max time for authentication has expired
     *     
     *     - Solar_Auth::IDLED: The authenticated user has been idle for too long
     *     
     *     - Solar_Auth::VALID: The user is authenticated and has not timed out
     *     
     *     - Solar_Auth::WRONG: The user attempted authentication but failed
     *   
     * - handle:  (string) The currently authenticated user handle.
     * 
     * - email:  (string) The email address of the currently authenticated user. May 
     *   or may not be populated by the adapter.
     * 
     * - moniker:  (string) The "display name" or "full name" of the currently 
     *   authenticated user.  May or may not be populated by the adapter.
     * 
     * - uri:  (string) The URI for the currently authenticated user. May or may not 
     *   be populated by the adapter.
     * 
     * - uid:  (mixed) The user ID (usually numeric) for the currently authenticated 
     *   user.  May or may not be populated by the adapter.
     * 
     * @var array
     * 
     * @see __get()
     * 
     * @see __set()
     * 
     */
    protected $_magic = array(
        'status',
        'initial',
        'active',
        'handle',
        'email',
        'moniker',
        'uri',
        'uid',
        'info',
    );
    
    protected $_login_protocol;
    
    protected $_logout_protocol;
    
    protected $_storage;
    
    /**
     * 
     * Modifies $this->_config after it has been built.
     * 
     * @return void
     * 
     */
    protected function _postConfig()
    {
        parent::_postConfig();
        
        // check max life before garbage collection on server vs. idle time
        $gc_maxlife = ini_get('session.gc_maxlifetime');
        if ($gc_maxlife < $this->_config['idle']) {
            throw $this->_exception('ERR_PHP_SESSION_IDLE', array(
                'session.gc_maxlifetime' => $gc_maxlife,
                'solar_auth_idle'      => $this->_config['idle'],
            ));
        }
        
        // check life at client vs. exipire time;
        // if life at client is zero, cookie never expires.
        $cookie_life = ini_get('session.cookie_lifetime');
        if ($cookie_life > 0 && $cookie_life < $this->_config['expire']) {
            throw $this->_exception('ERR_PHP_SESSION_EXPIRE', array(
                'session.cookie_lifetime' => $cookie_life,
                'solar_auth_expire'       => $this->_config['expire'],
            ));
        }
    }
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        // create the session object for this class
        $this->_session = Solar::factory(
            'Solar_Session',
            array('class' => get_class($this))
        );
        
        // make sure we have a login adapter
        $this->_login_protocol = Solar::dependency(
            'Solar_Auth_Login',
            $this->_config['login_protocol']
        );
        
        // make sure we have a logout adapter
        $this->_logout_protocol = Solar::dependency(
            'Solar_Auth_Logout',
            $this->_config['logout_protocol']
        );
        
        // make sure we have a storage adapter
        $this->_storage = Solar::dependency(
            'Solar_Auth_Storage',
            $this->_config['storage']
        );
    }
    
    /**
     * 
     * Magic get for pseudo-public properties as defined by [[Solar_Auth::$_magic]].
     * 
     * @param string $key The name of the property to get.
     * 
     * @return mixed
     * 
     * @see $_magic
     * 
     */
    public function __get($key)
    {
        if (! in_array($key, $this->_magic)) {
            throw $this->_exception('ERR_NO_SUCH_PROPERTY', array(
                'class'    => get_class($this),
                'property' => $key,
            ));
        }
        
        $val = $this->_session->get($key);
        
        // special behavior for 'status'
        if ($key == 'status' && ! $val) {
            $val = Solar_Auth::ANON;
        }
        
        // special behavior for 'info'
        if ($key == 'info' && ! $val) {
            $val = array();
        }
        
        return $val;
    }
    
    /**
     * 
     * Magic set for pseudo-public properties as defined by [[Solar_Auth::$_magic]].
     * 
     * @param string $key The name of the property to set.
     * 
     * @param mixed $val The value for the property.
     * 
     * @return void
     * 
     * @see $_magic
     * 
     */
    public function __set($key, $val)
    {
        if (! in_array($key, $this->_magic)) {
            throw $this->_exception('ERR_NO_SUCH_PROPERTY', array(
                'class'    => get_class($this),
                'property' => $key,
            ));
        }
        
        $this->_session->set($key, $val);
    }
    
    /**
     * 
     * determine which login protocol might be associated with this request
     * 
     * @return Solar_Auth_Protocol|null Returns the protocol object or 
     *      null if not a login request
     * 
     */
    public function getLoginProtocol()
    {
        if ($this->_login_protocol->isLoginRequest()) {
            return $this->_login_protocol->getProtocol();
        } else {
            return null;
        }
    }
    
    /**
     * 
     * determine which logout protocol might be associated with this request
     * 
     * @return Solar_Auth_Protocol|null Returns the protocol object or 
     *      null if not a login request
     * 
     */
    public function getLogoutProtocol()
    {
        if ($this->_logout_protocol->isLogoutRequest()) {
            return $this->_logout_protocol->getProtocol();
        } else {
            return null;
        }
    }
    
    /**
     * 
     * Starts authentication.
     * 
     * @return void
     * 
     */
    public function start()
    {
        // update idle and expire times no matter what
        $this->updateIdleExpire();
        
        // auto-login?
        if ($this->_config['auto_login'] && !$this->isValid()) {
            $protocol = $this->getLoginProtocol();
            return $this->processLogin($protocol);
        }
        
        // auto-logout?
        if ($this->_config['auto_logout'] && $this->isValid()) {
            $protocol = $this->getLogoutProtocol();
            return $this->processLogout($protocol);
        }
    }
    
    /**
     * 
     * Redirects to another URI after valid authentication.
     * 
     * @return void
     * 
     */
    protected function _redirect($href)
    {
        if ($href) {
            $response = Solar_Registry::get('response');
            $response->redirectNoCache($href);
            exit(0);
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
            
            // Check if authentication has expired
            $tmp = $this->initial + $this->_config['expire'];
            if ($this->_config['expire'] > 0 && $tmp < time()) {
                // past the expiration time
                // flash forward the status text, and return
                $this->reset(Solar_Auth::EXPIRED);
                return false;
            }
    
            // Check if user has been idle for too long
            $tmp = $this->active + $this->_config['idle'];
            if ($this->_config['idle'] > 0 && $tmp < time()) {
                // past the idle time
                // flash forward the status text, and return
                $this->reset(Solar_Auth::IDLED);
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
        return $this->status == Solar_Auth::VALID;
    }
    
    /**
     * 
     * Set user information. Only serializable information may be included.
     * 
     * @param array $info Array of user attributes
     * 
     * @return void
     * 
     */
    protected function _setInfo($info = array())
    {
        // baseline user information
        $base = array(
           'handle'  => null,
           'moniker' => null,
           'email'   => null,
           'uri'     => null,
           'uid'     => null,
        );
    
        // Set default values for require info keys        
        $info = array_merge($base, (array) $info);
    
        // Store any additional information gathered from the login protocol
        // or from the backend storage.
        $this->info = $info;
    
        // For historical reasons, some user attributes get their own properties        
        $this->handle  = $info['handle'];
        $this->moniker = $info['moniker'];
        $this->email   = $info['email'];
        $this->uri     = $info['uri'];
        $this->uid     = $info['uid'];
    }
    
    /**
     * 
     * Resets any authentication data in the session.
     * 
     * Typically used for idling, expiration, and logout.  Calls
     * [[php::session_regenerate_id() | ]] to clear previous session, if any
     * exists.
     * 
     * @param string $status A Solar_Auth status string; default is Solar_Auth::ANON.
     * 
     * @param array $info If status is Solar_Auth::VALID, populate properties with this
     * user data, with keys for 'handle', 'email', 'moniker', 'uri', and 
     * 'uid'.  If a key is empty or does not exist, its value is set to null.
     * 
     * @return void
     * 
     */
    public function reset($status = Solar_Auth::ANON, $info = array())
    {
        // canonicalize the status value
        $status = strtoupper($status);
        
        if (($this->status == self::ANON) && ($status == self::ANON)) {
            // If we are transitioning between anonymous and anonymous,
            // don't attempt to store information which would trigger
            // a session to start
            return;
        }
        
        // change the current status
        $this->status = $status;
        
        // change properties
        if ($this->status == Solar_Auth::VALID) {
            // update the timers, leave user info alone
            $now = time();
            $this->initial = $now;
            $this->active  = $now;
        } else {
            // clear the timers *and* the user info
            $this->initial = null;
            $this->active  = null;
            $info = null;
        }
        
        $this->_setInfo($info);
                
        // reset the session id and delete previous session
        $this->_session->regenerateId();
        
        // cache any messages
        $this->_session->set('status_text', $this->locale($this->status));
    }
    
    /**
     * 
     * Retrieve the status text from the session and then deletes it, making it
     * act like a read-once session flash value.
     * 
     * @return string The status text.
     * 
     */
    public function getStatusText()
    {
        $val = $this->_session->get('status_text');
        $this->_session->delete('status_text');
        return $val;
    }
    
    /**
     * 
     * Validate credentials against a list of back end storage adapters
     * 
     * @param array $credentials An array of authentication credentials
     * 
     * @return bool|array|string 
     * 
     */
    protected function _validateCredentials($credentials)
    {
        if (! $credentials) {
            // empty credentials are never valid
            return false;
        }
        
        $result = $this->_storage->validateCredentials($credentials);
        if ($result) {
            return $result;
        }
        
        return null;
    }
    
    /**
     * 
     * Validate that the protocol passed is a valid login protocol
     * in the current context.
     * Subclasses may add additional validation regarding which
     * protocol is valid in which context.
     * 
     * @param Solar_Auth_Login $protocol Login Protocol instance
     * 
     * @return bool True if protocol is valid
     * 
     */
    protected function _isValidLoginProtocol($protocol)
    {
        if (! $protocol) {
            return false;
        }
        return true;
    }
    
    /**
     * 
     * Reset authentication status based on a credential
     * validation result.
     * 
     * @param mixed $result Login An array result indicates success, anything 
     * else is a form of failure.
     * 
     * @return void
     * 
     */
    protected function _processCredentialStatus($result)
    {
        if (is_array($result)) {
            // successful login, treat result as user info
            $this->reset(Solar_Auth::VALID, $result);
        } elseif (is_string($result)) {
            // failed login, treat result as error code
            $this->reset($result);
        } else {
            // failed login, generic error code
            $this->reset(Solar_Auth::WRONG);
        }
    }
    
    /**
     * 
     * Process any login callbacks that might be registered.
     * Callbacks are called regardless of success or failure
     * of login.
     * 
     * @return void
     * 
     */
    protected function _processLoginCallbacks()
    {
        // callback?
        if ($this->_config['login_callback']) {
            call_user_func(
                $this->_config['login_callback'],
                $this
            );
        }
    }
    
    /**
     * 
     * Allow subclasses to perform pre Login processing
     * 
     * @return void
     * 
     */
    protected function _preLogin()
    {
    }
    
    /**
     * 
     * Allow subclasses to perform post login processing
     * 
     * @return void
     * 
     */
    protected function _postLoginSuccess()
    {
    }
    
    /**
     * 
     * Allow subclasses to perform post login processing
     * 
     * @return void
     * 
     */
    protected function _postLoginFailure()
    {
    }
    
    /**
     * 
     * Processes login attempts and sets user credentials.
     * This may be called as part of the autostart sequence or
     * explicitly called with a desired protocol to initiate
     * Authentication.
     * If you call processLogin outside of the autostart sequence,
     * You may also need to regenerate roles, access rules, and 
     * other data dependent upon the current authentication context.
     * 
     * @param Solar_Auth_Login $protocol Login Protocol instance
     * 
     * @return bool True if the login was successful, false if not.
     * 
     */
    public function processLogin($protocol)
    {
        if (!$this->_isValidLoginProtocol($protocol)) {
            return false;
        }
        
        $this->_preLogin();
    
        // load the user-provided credentials, such as handle and password
        $credentials = $protocol->getCredentials();
    
        $result = $this->_validateCredentials($credentials);
    
        $this->_processCredentialStatus($result);
        
        $this->_processLoginCallbacks();
        
        // did it work?
        if ($this->isValid()) {
        
            // Allow logic to be performed by subclasses upon success
            $this->_postLoginSuccess();
        
            // We must notify the credential providing protocol of success
            $protocol->postLoginSuccess();
            
            // attempt to redirect.
            $this->_redirect($protocol->getLoginRedirect());
            
            return true;
            
        } else {
    
            // Allow logic to be performed by subclasses upon failure
            $this->_postLoginFailure();
    
            // We must notify the credential providing protocol of failure
            $protocol->postLoginFailure();
            
            return false;
            
        }
    }
    
    /**
     * 
     * Validate that the protocol passed is a valid logout protocol
     * in the current context.
     * Subclasses may add additional validation regarding which
     * protocol is valid in which context.
     * 
     * @param Solar_Auth_Logout $protocol Logout Protocol instance
     * 
     * @return bool True if protocol is valid
     * 
     */
    protected function _isValidLogoutProtocol($protocol)
    {
        if (!$protocol) {
            return false;
        }
        return true;
    }
    
    /**
     * 
     * Process any logout callbacks that might be registered.
     * Callbacks are called regardless of success or failure
     * of logout.
     * 
     * @return void
     * 
     */
    protected function _processLogoutCallbacks()
    {
        // callback?
        if ($this->_config['logout_callback']) {
            call_user_func(
                $this->_config['logout_callback'],
                $this
            );
        }
    }
    
    /**
     * 
     * Allow subclasses to perform pre Logout processing
     * 
     * @return void
     * 
     */
    protected function _preLogout()
    {
    }
    
    /**
     * 
     * Allow subclasses to perform post logout processing
     * 
     * @return void
     * 
     */
    protected function _postLogout()
    {
    }
    
    /**
     * 
     * Processes logout attempts.
     * 
     * @return void
     * 
     */
    public function processLogout($protocol)
    {
        if (!$this->_isValidLogoutProtocol($protocol)) {
            return false;
        }
    
        $this->_preLogout();
    
        $this->_processLogout();
    
        $this->_processLogoutCallbacks();
        
        $this->_postLogout();
        
        // See if a redirect is needed
        $this->_redirect($protocol->getLogoutRedirect());
    }
    
    /**
     * 
     * Logout processing.
     * 
     * @return void
     * 
     */
    protected function _processLogout()
    {
        // change status
        $this->reset(Solar_Auth::ANON);
        
        // End the entire session
        $this->_session->stop();
    }
}