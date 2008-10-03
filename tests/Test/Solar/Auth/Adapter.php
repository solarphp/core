<?php
/**
 * 
 * Abstract class test.
 * 
 */
abstract class Test_Solar_Auth_Adapter extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Adapter = array(
    );
    
    protected $_auth;
    
    protected $_class;
    
    protected $_post;
    
    protected $_handle = 'pmjones';
    
    protected $_email = null;
    
    protected $_moniker = null;
    
    protected $_uri = null;
    
    protected $_request;
    
    // -----------------------------------------------------------------
    // 
    // Support methods.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration parameters.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    /**
     * 
     * Destructor; runs after all methods are complete.
     * 
     * @param array $config User-defined configuration parameters.
     * 
     */
    public function __destruct()
    {
        parent::__destruct();
    }
    
    /**
     * 
     * Setup; runs before each test method.
     * 
     */
    public function setup()
    {
        parent::setup();
        
        // remove "Test_" prefix
        $this->_class = substr(get_class($this), 5);
        
        // get the request environment
        $this->_request = Solar_Registry::get('request');
        
        // get a new adapter
        $this->_auth = Solar::factory($this->_class, $this->_config);
    }
    
    /**
     * 
     * Setup; runs after each test method.
     * 
     */
    public function teardown()
    {
        parent::teardown();
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
    
    // -----------------------------------------------------------------
    // 
    // Test methods.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Test -- Constructor.
     * 
     */
    public function test__construct()
    {
        $this->assertInstance($this->_auth, $this->_class);
    }
    
    public function test_handle()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->processLogin());
        $this->assertSame($this->_auth->handle, $this->_handle);
    }
    
    public function test_email()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->processLogin());
        $this->assertSame($this->_auth->email, $this->_email);
    }
    
    public function test_moniker()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->processLogin());
        $this->assertSame($this->_auth->moniker, $this->_moniker);
    }
    
    public function test_uri()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->processLogin());
        $this->assertSame($this->_auth->uri, $this->_uri);
    }
    
    /**
     * 
     * Test -- Retrieves a "read-once" session value for Solar_Auth.
     * 
     */
    public function testGetFlash()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Tells if the current page load appears to be the result of an attempt to log in.
     * 
     */
    public function testIsLoginRequest()
    {
        // fake the POST parameters
        $this->_request->post['process'] = $this->_auth->locale('PROCESS_LOGIN');
        $this->assertTrue($this->_auth->isLoginRequest());
    }
    
    public function testIsLoginRequest_false()
    {
        $this->assertFalse($this->_auth->isLoginRequest());
    }
    
    /**
     * 
     * Test -- Tells if the current page load appears to be the result of an attempt to log out.
     * 
     */
    public function testIsLogoutRequest()
    {
        // fake the POST parameters
        $this->_request->post['process'] = $this->_auth->locale('PROCESS_LOGOUT');
        $this->assertTrue($this->_auth->isLogoutRequest());
    }
    
    public function testIsLogoutRequest_false()
    {
        $this->assertFalse($this->_auth->isLogoutRequest());
    }
    
    /**
     * 
     * Test -- Tells whether the current authentication is valid.
     * 
     */
    public function testIsValid()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Processes login attempts and sets user credentials.
     * 
     */
    public function testProcessLogin()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->processLogin());
    }
    
    public function testProcessLogin_badPasswd()
    {
        $this->_fakePostLogin_badPasswd();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->processLogin());
    }
    
    public function testProcessLogin_noSuchUser()
    {
        $this->_fakePostLogin_noSuchUser();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->processLogin());
    }
    
    /**
     * 
     * Test -- Processes logout attempts.
     * 
     */
    public function testProcessLogout()
    {
        $this->todo('stub');
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
     * Test -- Starts a session with authentication.
     * 
     */
    public function testStart()
    {
        $this->todo('stub');
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