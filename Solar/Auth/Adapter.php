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
     * User-supplied configuration values.
     * 
     * Keys are ...
     * 
     * `expire`
     * : (int) Authentication lifetime in seconds; zero is
     *   forever.  Default is 14400 (4 hours).
     * 
     * `idle`
     * : (int) Maximum allowed idle time in seconds; zero is
     *   forever.  Default is 1800 (30 minutes).
     * 
     * `allow`
     * : (bool) Whether or not to allow automatic login/logout at start()
     *   time. Default true.
     * 
     * `source`
     * : (string) The source for auth credentials, 'get' (via the
     *   for GET request vars) or 'post' (via the POST request vars).
     *   Default is 'post'.
     * 
     * `source_handle`
     * : (string) Username key in the credential array source,
     *   default 'handle'.
     * 
     * `source_passwd`
     * : (string) Password key in the credential array source,
     *   default 'passwd'.
     * 
     * `source_redirect`
     * : (string) Element key in the credential array source to indicate
     *   where to redirect on successful login or logout, default 'redirect'.
     * 
     * `source_process`
     * : (string) Element key in the credential array source to indicate
     *   how to process the request, default 'process'.
     * 
     * `process_login`
     * : (string) The source_process element value indicating a login request;
     *   default is the 'PROCESS_LOGIN' locale key value.
     * 
     * `process_logout`
     * : (string) The source_process element value indicating a logout request;
     *   default is the 'PROCESS_LOGOUT' locale key value.
     * 
     * `session_class`
     * : (string) The class name to use as the session storage segment name.
     *   Default is 'Solar_Auth_Adapter' regardless of the actual class name
     *   (this lets multiple adapters share the same credential information).
     * 
     * @var array
     * 
     */
    protected $_Solar_Auth_Adapter = array(
        'expire'         => 14400,
        'idle'           => 1800,
        'allow'          => true,
        'source'         => 'post',
        'source_handle'  => 'handle',
        'source_passwd'  => 'passwd',
        'source_redirect' => 'redirect',
        'source_process' => 'process',
        'process_login'  => null,
        'process_logout' => null,
        'session_class'  => 'Solar_Auth_Adapter',
    );
    
    /**
     * 
     * Details on the current request.
     * 
     * @var Solar_Request
     * 
     */
    protected $_request;
    
    /**
     * 
     * Class-specific session object.
     * 
     * @var Solar_Session
     * 
     */
    protected $_session;
    
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
     * Magic "public" properties that are actually parts of the session
     * segment.
     * 
     * The available magic properties are:
     * 
     * `status`
     * : (string) The Unix time at which the authenticated handle was last 
     *   valid.
     * 
     * `initial`
     * : (int) The Unix time at which the handle was initially authenticated.
     * 
     * `active`
     * : (int) The status code of the current user authentication. The string
     *   codes are ...
     *   
     *     `ANON` (or empty)
     *     : The user is anonymous/unauthenticated (no attempt to 
     *       authenticate)
     *     
     *     `EXPIRED`
     *     : The max time for authentication has expired
     *     
     *     `IDLED`
     *     : The authenticated user has been idle for too long
     *     
     *     `VALID`
     *     : The user is authenticated and has not timed out
     *     
     *     `WRONG`
     *     : The user attempted authentication but failed
     *   
     * 
     * `handle`
     * : (string) The currently authenticated user handle.
     * 
     * `email`
     * : (string) The email address of the currently authenticated user. May 
     *   or may not be populated by the adapter.
     * 
     * `moniker`
     * : (string) The "display name" or "full name" of the currently 
     *   authenticated user.  May or may not be populated by the adapter.
     * 
     * `uri`
     * : (string) The URI for the currently authenticated user. May or may not 
     *   be populated by the adapter.
     * 
     * `uid`
     * : (mixed) The user ID (usually numeric) for the currently authenticated 
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
     * Constructor.
     * 
     * @param array $config An array of user-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // make sure we have process values
        if (empty($this->_config['process_login'])) {
            $this->_config['process_login'] = $this->locale('PROCESS_LOGIN');
        }
        
        if (empty($this->_config['process_logout'])) {
            $this->_config['process_logout'] = $this->locale('PROCESS_LOGOUT');
        }
        
        // make sure the source is either 'get' or 'post'.
        $is_get_or_post = $this->_config['source'] == 'get' 
                       || $this->_config['source'] == 'post';
                       
        if (! $is_get_or_post) {
            // default to post
            $this->_config['source'] = 'post';
        }
        
        // make sure we have a session class name; this determines how the
        // session store is segmented.  when you have multiple adapters that
        // need to use the same store, this is useful.
        if (! $this->_config['session_class']) {
            $this->_config['session_class'] = 'Solar_Auth_Adapter';
        }
        
        // get the current request environment
        $this->_request = Solar_Registry::get('request');
        
        // set per config
        $this->allow = (bool) $this->_config['allow'];
        
        // create the session-access object.
        // starts the session if it has not been started already.
        $this->_session = Solar::factory(
            'Solar_Session',
            array('class' => $this->_config['session_class'])
        );
    }
    
    /**
     * 
     * Magic get for pseudo-public properties.
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
                'key' => $key,
            ));
        }
        
        $val = $this->_session->get($key);
        
        // special behavior for 'status'
        if ($key == 'status' && ! $val) {
            $val = Solar_Auth::ANON;
        }
        
        return $val;
    }
    
    /**
     * 
     * Magic set for pseudo-public properties.
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
                'key' => $key,
            ));
        }
        
        $this->_session->set($key, $val);
    }
    
    /**
     * 
     * Starts a session with authentication.
     * 
     * @return void
     * 
     */
    public function start()
    {
        // update idle and expire times no matter what
        $this->updateIdleExpire();
        
        // allow auto-processing?
        if (! $this->allow) {
            return;
        }
        
        // auto-login
        if (! $this->isValid() && $this->isLoginRequest()) {
            // process login attempt
            $this->processLogin();
            // did it work?
            if ($this->isValid()) {
                // attempt to redirect.
                $this->_redirect();
            }
            // done, do not try to process a logout after this ;-)
            return;
        }
        
        // auto-logout
        if ($this->isValid() && $this->isLogoutRequest()) {
            // process logout attempts
            $this->processLogout();
            // logout always works ;-) so attempt to redirect
            $this->_redirect();
        }
        
    }
    
    /**
     * 
     * Redirects to another URI after valid authentication.
     * 
     * Looks at the value of the 'redirect' source key, and sets a 'Location:'
     * header from it.  Note that this will end any further processing on this
     * page-load.
     * 
     * If the 'redirect' key is empty or not present, will not redirect, and
     * processing will continue.
     * 
     * @return void
     * 
     */
    protected function _redirect()
    {
        $method = strtolower($this->_config['source']);
        $href = $this->_request->$method($this->_config['source_redirect']);
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
            
            // Check if session authentication has expired
            $tmp = $this->initial + $this->_config['expire'];
            if ($this->_config['expire'] > 0 && $tmp < time()) {
                // past the expiration time
                // flash forward the status text, and return
                $this->reset(Solar_Auth::EXPIRED);
                return false;
            }
    
            // Check if session has been idle for too long
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
     * Resets any authentication data in the session.
     * 
     * Note that this will start a session if one is not already in progress.
     * 
     * Typically used for idling, expiration, and logout.  Calls
     * [[php::session_regenerate_id() | ]] to clear previous session.
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
        
        // reset the session id and delete previous session file
        $this->_session->regenerateId();
        
        // flash forward any messages
        $this->_session->setFlash('status_text', $this->locale($this->status));
    }
    
    /**
     * 
     * Retrieves a "read-once" session value for Solar_Auth.
     * 
     * Starts a session if one is not already going.
     * 
     * Typical key here is "status_text".
     * 
     * @param string $key The specific type of information.
     * 
     * @param mixed $val If the key does not exist, return
     * this value.  Default null.
     * 
     * @return mixed The "read-once" value.
     * 
     */
    public function getFlash($key, $val = null)
    {
        return $this->_session->getFlash($key, $val);
    }
    
    /**
     * 
     * Tells if the current page load appears to be the result of
     * an attempt to log in.
     * 
     * @return bool
     * 
     */
    public function isLoginRequest()
    {
        $method = strtolower($this->_config['source']);
        $process = $this->_request->$method($this->_config['source_process']);
        return $process == $this->_config['process_login'];
    }
    
    /**
     * 
     * Tells if the current page load appears to be the result of
     * an attempt to log out.
     * 
     * @return bool
     * 
     */
    public function isLogoutRequest()
    {
        $method = strtolower($this->_config['source']);
        $process = $this->_request->$method($this->_config['source_process']);
        return $process == $this->_config['process_logout'];
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
            return true;
        } elseif (is_string($result)) {
            // failed login, treat result as error code
            $this->reset($result);
            return false;
        } else {
            // failed login, generic error code
            $this->reset(Solar_Auth::WRONG);
            return false;
        }
    }
    
    /**
     * 
     * Loads the user credentials (handle and passwd) from the request source.
     * 
     * @return void
     * 
     */
    protected function _loadCredentials()
    {
        // where do the handle and passwd come from?
        $method = strtolower($this->_config['source']);
        
        // retrieve the handle and passwd
        $this->_handle = $this->_request->$method($this->_config['source_handle']);
        $this->_passwd = $this->_request->$method($this->_config['source_passwd']);
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
        $code = $this->_processLogout();
        $this->reset($code);
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
