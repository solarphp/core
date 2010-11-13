<?php
/**
 * 
 * Session manager for native PHP sessions
 * 
 * @category Solar
 * 
 * @package Solar_Session
 * 
 * @author Antti Holvikari <anttih@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Native.php 3366 2008-08-26 01:36:49Z pmjones $
 * 
 */
class Solar_Session_Manager_Adapter_Native extends Solar_Session_Manager_Adapter
{

    /**
     * 
     * Default configuration values.
     * 
     * @config dependency handler A Solar_Session_Handler dependency injection. Default
     *   is the string 'php', which means to use the native PHP session save.
     *   handler instead of a dependency injection.
     * 
     * @var array
     * 
     */
    protected $_Solar_Session_Manager_Adapter_Native = array(
        'handler' => null,
    );

    /**
     * 
     * The session save handler object.
     * 
     * @var Solar_Session_Handler_Adapter
     * 
     */
    static protected $_handler;

    
    /**
     * 
     * The current request object.
     * 
     * @var Solar_Request
     * 
     */
    protected $_request;

    /**
     * 
     * A list of sessions.
     * 
     * @var array
     * 
     */
    protected $_sessions = array();

    /**
     * 
     * Has a session already been stopped in this request?
     * 
     * @var bool
     * 
     */
    protected $_stopped = false;

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
        
        // only set up the handler if it doesn't exist yet.
        if (! self::$_handler) {
            self::$_handler = Solar::dependency(
                'Solar_Session_Handler',
                $this->_config['handler']
            );
        }

        $this->_request = Solar_Registry::get('request');
    }

    /**
     * 
     * unload all related sessions
     * 
     * @return bool
     * 
     */
    public function _unloadAll()
    {
        foreach($this->_sessions as $segment) {
            $segment->unload();
        }
    }
    
    /**
     * 
     * Starts the session
     * 
     * @return void
     * 
     */
    public function start()
    {
        session_start();
    }

    /**
     * 
     * Regenerates the session ID.
     * 
     * Use this every time there is a privilege change.
     * 
     * @return void
     * 
     * @see [[php::session_regenerate_id()]]
     * 
     */
    public function regenerateId()
    {
        session_regenerate_id(true);
    }

    /**
     * 
     * Has a session been started yet?
     * 
     * @return bool
     * 
     */
    public function isStarted()
    {
        return session_id() !== '';
    }    

    /**
     * 
     * Has the user requested a prior session?
     * 
     * @return bool
     * 
     */
    public function isContinuing()
    {
        if ($this->_stopped) {
            // Don't attempt to continue a session we've already destroyed
            return false;
        }
        $name = session_name();
        return $this->_request->cookie($name);
    }

    /**
     * 
     * Remove this session and kill the cookie
     * 
     * @return bool
     * 
     */
    public function stop()
    {
        // remove the session cookie
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']);

        // Kill the backend storage of the session
        session_destroy();
        
        // Let all the sessions know that their data is no longer valid
        $this->_unloadAll();
        
        // We've already processed one session during this request
        $this->_stopped = true;
    }

    /**
     * 
     * Allow sessions to register with the mothership
     * 
     * @return void
     * 
     */
    public function addSession(Solar_Session $session)
    {
        $this->_sessions[] = $session;
    }

    /**
     * 
     * Close this session for use in this request, writing the results
     * to storage for the next request
     * 
     * @return void
     * 
     */
    public function close()
    {
        session_write_close();

        // Let all the sessions know that their data is no longer valid
        $this->_unloadAll();
        
        // clean out the session data, further changes to $_SESSION will not
        // be written out unless the sesion is restarted
        $_SESSION = array();
    }

}