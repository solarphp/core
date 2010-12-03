<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Auth extends Solar_Test {
    
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth = array(
    );
    
    protected $_request;
    
    protected $_auth;
    
    public function preTest()
    {
        Solar_Registry::set('session_manager', 'Solar_Session_Manager');
        $this->_request = Solar_Registry::get('request');
        $this->_auth = $this->_newAuth();
    }
    
    protected function _newAuth()
    {
        $storage = Solar::factory('Solar_Auth_Storage', array(
            'adapter' => 'Solar_Auth_Storage_Adapter_Var',
            'data' => array(
                // username => password
                'pmjones' => 'jones',
            )
        ));
        
        $auth = Solar::factory('Solar_Auth', array('storage' => $storage));
        return $auth;
    }
    
    protected function _fakePostLogin_valid()
    {
        $this->_request->post['process'] = $this->_auth->locale('PROCESS_LOGIN');
        $this->_request->post['handle'] = 'pmjones';
        $this->_request->post['passwd'] = 'jones';
    }
    
    protected function _fakePostLogin_badPasswd()
    {
        $this->_request->post['process'] = $this->_auth->locale('PROCESS_LOGIN');
        $this->_request->post['handle'] = 'pmjones';
        $this->_request->post['passwd'] = 'badpass';
    }
    
    protected function _fakePostLogin_noSuchUser()
    {
        $this->_request->post['process'] = $this->_auth->locale('PROCESS_LOGIN');
        $this->_request->post['handle'] = 'nouser';
        $this->_request->post['passwd'] = 'badpass';
    }
    
    protected function _fakePostLogout()
    {
        $this->_request->post['process'] = $this->_auth->locale('PROCESS_LOGOUT');
    }
    
    /**
     * 
     * Test -- Constructor.
     * 
     */
    public function test__construct()
    {
        $expect = 'Solar_Auth';
        $this->assertInstance($this->_auth, $expect);
    }
    
    /**
     * 
     * Test -- Magic get for pseudo-public properties as defined by [[Solar_Auth::$_magic]].
     * 
     */
    public function test__get()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Magic set for pseudo-public properties as defined by [[Solar_Auth::$_magic]].
     * 
     */
    public function test__set()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- determine which login protocol might be associated with this request
     * 
     */
    public function testGetLoginProtocol()
    {
        $this->_fakePostLogin_valid();
        $actual = $this->_auth->getLoginProtocol();
        $expect = 'Solar_Auth_Login_Adapter_Post';
        $this->assertInstance($actual, $expect);
    }
    
    /**
     * 
     * Test -- determine which logout protocol might be associated with this request
     * 
     */
    public function testGetLogoutProtocol()
    {
        $this->_fakePostLogout();
        $actual = $this->_auth->getLogoutProtocol();
        $expect = 'Solar_Auth_Logout_Adapter_Post';
        $this->assertInstance($actual, $expect);
    }
    
    /**
     * 
     * Test -- Retrieve the status text from the session and then deletes it, making it act like a read-once session flash value.
     * 
     */
    public function testGetStatusText()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Tells whether the current authentication is valid.
     * 
     */
    public function testIsValid()
    {
        $this->_fakePostLogin_valid();
        $this->_auth->start();
        $this->assertTrue($this->_auth->isValid());
    }
    
    /**
     * 
     * Test -- Processes login attempts and sets user credentials.
     * 
     */
    public function testProcessLogin()
    {
        $this->_fakePostLogin_valid();
        $protocol = $this->_auth->getLoginProtocol();
        $this->assertTrue($protocol instanceof Solar_Auth_Login_Adapter);
        $this->assertTrue($this->_auth->processLogin($protocol));
        $this->assertTrue($this->_auth->isValid());
    }
    
    public function testProcessLogin_badPasswd()
    {
        $this->_fakePostLogin_badPasswd();
        $protocol = $this->_auth->getLoginProtocol();
        $this->assertTrue($protocol instanceof Solar_Auth_Login_Adapter);
        $this->assertFalse($this->_auth->processLogin($protocol));
        $this->assertFalse($this->_auth->isValid());
    }
    
    public function testProcessLogin_noSuchUser()
    {
        $this->_fakePostLogin_noSuchUser();
        $protocol = $this->_auth->getLoginProtocol();
        $this->assertTrue($protocol instanceof Solar_Auth_Login_Adapter);
        $this->assertFalse($this->_auth->processLogin($protocol));
        $this->assertFalse($this->_auth->isValid());
    }
    
    
    /**
     * 
     * Test -- Processes logout attempts.
     * 
     */
    public function testProcessLogout()
    {
        $this->testProcessLogin();
        $this->_fakePostLogout();
        $protocol = $this->_auth->getLogoutProtocol();
        $this->assertTrue($protocol instanceof Solar_Auth_Logout_Adapter);
        $this->_auth->processLogout($protocol);
        $this->assertFalse($this->_auth->isValid());
    }
    
    /**
     * 
     * Test -- Resets any authentication data in the session.
     * 
     */
    public function testReset()
    {
        $this->_auth->reset();
        $this->assertNull($this->_auth->handle);
        $this->assertNull($this->_auth->email);
        $this->assertNull($this->_auth->moniker);
        $this->assertNull($this->_auth->uri);
        $this->assertNull($this->_auth->uid);
    }
    
    /**
     * 
     * Test -- Starts authentication.
     * 
     */
    public function testStart()
    {
        $this->_fakePostLogin_valid();
        $this->_auth->start();
        $this->assertTrue($this->_auth->isValid());
    }
    
    public function testStart_anon()
    {
        $this->_auth->start();
        $this->assertFalse($this->_auth->isValid());
    }
    
    public function testStart_logout()
    {
        $this->_fakePostLogin_valid();
        $this->_auth->start();
        $this->assertTrue($this->_auth->isValid());
        
        $this->_fakePostLogout();
        $this->_auth->start();
        $this->assertFalse($this->_auth->isValid());
    }
    
    
    /**
     * 
     * Test -- Updates idle and expire times, invalidating authentication if they are exceeded.
     * 
     */
    public function testUpdateIdleExpire()
    {
        $this->todo('stub');
    }
}
