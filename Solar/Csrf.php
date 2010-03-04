<?php
class Solar_Csrf extends Solar_Base
{
    static protected $_current = null;
    
    static protected $_key = '__csrf_key';
    
    static protected $_request;
    
    static protected $_session;
    
    static protected $_updated = false;
    
    protected function _postConstruct()
    {
        if (! self::$_session) {
            self::$_session = Solar::factory('Solar_Session', array(
                'class' => 'Solar_Csrf',
            ));
        }
        
        if (! self::$_request) {
            self::$_request = Solar_Registry::get('request');
        }
        
        // ignore construct-time configuration for the key, but honor
        // it from the config file.  we want the key name to be the
        // same everywhere all the time.
        $key = Solar_Config::get('Solar_Csrf', 'key');
        if ($key) {
            self::$_key = $key;
        }
    }
    
    protected function _update()
    {
        if (self::$_updated) {
            // already updated with current values
            return;
        }
        
        // lazy-start the session if one exists
        self::$_session->lazyStart();
        if (! self::$_session->isStarted()) {
            // not started, nothing left to do
            return;
        }
        
        // the session has started. is there an existing csrf token?
        if (self::$_session->has('token')) {
            // retain the existing token
            self::$_current = self::$_session->get('token');
        } else {
            // no token, create a new one for the session.
            // we're transitioning from a non-token state, and
            // incoming forms won't have it yet, so we don't retain
            // the new token as the current value.
            self::$_session->set('token', uniqid(mt_rand(), true));
        }
        
        self::$_updated = true;
    }
    
    public function getKey()
    {
        return self::$_key;
    }
    
    // gets the outgoing token value
    public function getToken()
    {
        $this->_update();
        return self::$_session->get('token');
    }
    
    // sets the outgoing token value
    public function setToken($token)
    {
        $this->_update();
        self::$_session->set('token', $token);
    }
    
    // does a token exist?
    public function hasToken()
    {
        $this->_update();
        return self::$_session->has('token');
    }
    
    // resets the outgoing token value
    public function resetToken()
    {
        self::set(uniqid(mt_rand(), true));
    }
    
    // gets the current value
    public function getCurrent()
    {
        $this->_update();
        return self::$_current;
    }
    
    // does the current request look like a forgery?
    public function isForgery()
    {
        $this->_update();
        
        if (! self::$_request->isPost()) {
            // only POST requests can be cross-site request forgeries
            return false;
        }
        
        if (! self::$_current) {
            // there is no current value so it doesn't matter
            return false;
        }
        
        // get the incoming csrf value from $_POST
        $key = $this->getKey();
        $val = self::$_request->post($key);
        
        // if they don't match, it's a forgery
        return $val != self::$_current;
    }
}