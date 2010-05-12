<?php
/**
 * 
 * Abstract authentication adapter.
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
abstract class Solar_Auth_Adapter extends Solar_Base {
    
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
     * @config bool allow Whether or not to allow automatic login/logout at start()
     *   time. Default true.
     * 
     * @config dependency cache A Solar_Cache dependency to store user data. Default is
     *   to create a Solar_Cache_Adapter_Session object internal to this 
     *   instance.
     * 
     * @config callback login_callback A callback to execute after successful login, but before
     *   the source postLogin() method is called.
     * 
     * @config callback logout_callback A callback to execute after successful logout, but before
     *   the source postLogout() method is called.
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Adapter = array(
        'expire'         => 14400,
        'idle'           => 1440,
        'allow'          => true,
        'cache' => array(
            'adapter' => 'Solar_Cache_Adapter_Session',
            'prefix'  => 'Solar_Auth_Adapter',
        ),
        'login_callback'  => null,
        'logout_callback' => null,
        'protocol' => 'Solar_Auth_Protocol_Post',
    );
    
    /**
     * 
     * A cache object to retain the current user information.
     * 
     * @var Solar_Cache_Adapter
     * 
     */
    protected $_cache;

    /**
     * 
     * The protocol used to extract credentials from a request.
     * 
     * @var Solar_Auth_Protocol
     * 
     */
    protected $_protocol;
    
    /**
     * 
     * The user-provided plaintext handle, if any.
     * 
     * @var string
     * 
     */
    protected $_handle;
    
    /**
     * 
     * The user-provided plaintext password, if any.
     * 
     * @var string
     * 
     */
    protected $_passwd;
    
    /**
     * 
     * The current error code string.
     * 
     * @var string
     * 
     */
    protected $_err;
    
    /**
     * 
     * Whether or not to allow automatic login/logout at start() time.
     * 
     * @var bool
     * 
     */
    public $allow = true;
    
    /**
     * 
     * Magic "public" properties that are actually stored in the cache.
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
    );
    
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
        parent::_postConstruct();
        
        // set per config
        $this->allow = (bool) $this->_config['allow'];
        
        // cache dependency injection
        $this->_cache = Solar::dependency(
            'Solar_Cache',
            $this->_config['cache']
        );

        // Setup the authentication protocol
        $this->_protocol = Solar::factory($this->_config['protocol']);
    }
    
    /**
     * 
     * Magic get for pseudo-public properties as defined by [[Solar_Auth_Adapter::$_magic]].
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
        
        $val = $this->_cache->fetch($key);
        
        // special behavior for 'status'
        if ($key == 'status' && ! $val) {
            $val = Solar_Auth::ANON;
        }
        
        return $val;
    }
    
    /**
     * 
     * Magic set for pseudo-public properties as defined by [[Solar_Auth_Adapter::$_magic]].
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
        
        $this->_cache->save($key, $val);
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
        
        // allow auto-processing?
        if (! $this->isAllowed()) {
            return;
        }
        
        // auto-login?
        if (! $this->isValid() && $this->_protocol->isLoginRequest()) {
            return $this->processLogin();
        }
        
        // auto-logout?
        if ($this->isValid() && $this->_protocol->isLogoutRequest()) {
            return $this->processLogout();
        }
    }
    
    /**
     * 
     * Redirects to another URI after valid authentication.
     * 
     * @return void
     * 
     */
    protected function _loginRedirect()
    {
        $href = $this->_protocol->getLoginRedirect();
        if ($href) {
            $response = Solar_Registry::get('response');
            $response->redirectNoCache($href);
            exit(0);
        }
    }

    /**
     * 
     * Redirects to another URI after valid authentication.
     * 
     * @return void
     * 
     */
    protected function _logoutRedirect()
    {
        $href = $this->_protocol->getLogoutRedirect();
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
     * Tells whether authentication processing is allowed.
     * 
     * @return bool Whether or not authentication processing is allowed.
     * 
     */
    public function isAllowed()
    {
        return (bool) $this->allow;
    }
    
    /**
     * 
     * Resets any authentication data in the cache.
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
        // baseline user information
        $base = array(
           'handle'  => null,
           'moniker' => null,
           'email'   => null,
           'uri'     => null,
           'uid'     => null,
        );
        
        // reset the status
        $this->status = strtoupper($status);
        
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
        
        // set the user-info properties
        $info = array_merge($base, (array) $info);
        $this->handle  = $info['handle'];
        $this->moniker = $info['moniker'];
        $this->email   = $info['email'];
        $this->uri     = $info['uri'];
        $this->uid     = $info['uid'];
        
        // reset the session id and delete previous session, but only
        // if a session is actually in place
        if (session_id() !== '' && ! headers_sent()) {
            session_regenerate_id();
        }
        
        // cache any messages
        $this->_cache->save('status_text', $this->locale($this->status));
    }
    
    /**
     * 
     * Retrieve the status text from the cache and then deletes it, making it
     * act like a read-once session flash value.
     * 
     * @return string The status text.
     * 
     */
    public function getStatusText()
    {
        $val = $this->_cache->fetch('status_text');
        $this->_cache->delete('status_text');
        return $val;
    }

    /**
     * 
     * Loads the user credentials (handle and passwd) from the Authentication Protocol.
     * 
     * @return void
     * 
     */
    protected function _loadCredentials()
    {
        $credentials = $this->_protocol->getCredentials();
        
        // retrieve the handle and passwd
        $this->_handle = $credentials['handle'];
        $this->_passwd = $credentials['passwd'];
    }
    
    /**
     * 
     * Processes login attempts and sets user credentials.
     * 
     * @return bool True if the login was successful, false if not.
     * 
     */
    public function processLogin()
    {
        // clear out current error and user data.
        $this->_err = null;
        $this->reset();
        
        // load the user-provided handle and password
        $this->_loadCredentials();
        
        // adapter-specific login processing
        $result = $this->_processLogin();
        
        // did it work?
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
        
        // callback?
        if ($this->_config['login_callback']) {
            call_user_func(
                $this->_config['login_callback'],
                $this
            );
        }
        
        // did it work?
        if ($this->isValid()) {
        
            // We were successfully logged in
            $this->_protocol->postLoginSuccess();
            
            // attempt to redirect.
            $this->_loginRedirect();
        } else {

            // We failed
            $this->_protocol->postLoginFailure();
        }
        
        // done!
        return $this->status == Solar_Auth::VALID;
    }
    
    /**
     * 
     * Adapter-specific login processing.
     * 
     * @return mixed An array of user information if valid; if not valid, a
     * string error code or empty value.
     * 
     */
    protected function _processLogin()
    {
        // you should implement this in an adapter, but the default is to 
        // not-validate login attempts.
        return false;
    }
    
    /**
     * 
     * Processes logout attempts.
     * 
     * @return void
     * 
     */
    public function processLogout()
    {
        // process logout
        $code = $this->_processLogout();
        
        // change status
        $this->reset($code);
        
        // callback?
        if ($this->_config['logout_callback']) {
            call_user_func(
                $this->_config['logout_callback'],
                $this
            );
        }
        
        // logout always works, so see if a redirect is needed
        $this->_logoutRedirect();
    }
    
    /**
     * 
     * Adapter-specific logout processing.
     * 
     * @return string A status code string for reset().
     * 
     */
    protected function _processLogout()
    {
        return Solar_Auth::ANON;
    }
}
