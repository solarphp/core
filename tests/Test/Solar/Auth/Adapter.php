<?php

abstract class Test_Solar_Auth_Adapter extends Solar_Test {
    
    protected $_auth;
    
    protected $_class;
    
    protected $_post;
    
    protected $_handle = 'pmjones';
    
    protected $_email = null;
    
    protected $_moniker = null;
    
    protected $_uri = null;
    
    protected $_request;
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // convert from Test_Solar_Auth_Adapter_Whatever
        // to Solar_Auth_Adapter_Whatever
        $this->_class = substr(get_class($this), 5);
    }
    
    public function __destruct()
    {
        parent::__destruct();
    }
    
    public function setup()
    {
        // get a new adapter
        $this->_auth = Solar::factory($this->_class, $this->_config);
        
        // get the request environment ...
        $this->_request = Solar::factory('Solar_Request');
        
        // and reload it fresh for this test.
        $this->_request->load(true);
    }
    
    public function teardown()
    {
        parent::teardown();
    }
    
    public function test__construct()
    {
        $this->assertInstance($this->_auth, $this->_class);
    }
    
    public function testSetCommon()
    {
        // non-default common values
        $common = array(
            'source'        => 'get',
            'source_handle' => 'username',
            'source_passwd' => 'password',
            'source_submit' => 'myaction',
            'submit_login'  => 'Log In',
            'submit_logout' => 'Log Out',
        );
        
        $this->_auth->setCommon($common);
        $this->assertProperty($this->_auth, '_common', 'same', $common);
        
        // make sure you get 'post' source by default
        $common['source'] = 'no-such-source';
        $expect = $common;
        $expect['source'] = 'post';
        $this->_auth->setCommon($common);
        $this->assertProperty($this->_auth, '_common', 'same', $expect);
    }
    
    public function testIsLoginRequest_true()
    {
        // fake the POST parameters
        $this->_request->post['submit'] = $this->_auth->locale('SUBMIT_LOGIN');
        $this->assertTrue($this->_auth->isLoginRequest());
    }
    
    public function testIsLoginRequest_false()
    {
        $this->assertFalse($this->_auth->isLoginRequest());
    }
    
    public function testIsLogoutRequest_true()
    {
        // fake the POST parameters
        $this->_request->post['submit'] = $this->_auth->locale('SUBMIT_LOGOUT');
        $this->assertTrue($this->_auth->isLogoutRequest());
    }
    
    public function testIsLogoutRequest_false()
    {
        $this->assertFalse($this->_auth->isLogoutRequest());
    }
    
    public function testIsLoginValid_true()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->isLoginValid());
    }
    
    public function testIsLoginValid_badPasswd()
    {
        $this->_fakePostLogin_badPasswd();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->isLoginValid());
    }
    
    public function testIsLoginValid_noSuchUser()
    {
        $this->_fakePostLogin_noSuchUser();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->isLoginValid());
    }
    
    public function testReset()
    {
        $this->_auth->reset();
        $this->assertProperty($this->_auth, '_handle', 'null');
        $this->assertProperty($this->_auth, '_passwd', 'null');
        $this->assertProperty($this->_auth, '_email', 'null');
        $this->assertProperty($this->_auth, '_moniker', 'null');
        $this->assertProperty($this->_auth, '_uri', 'null');
    }
    
    public function testGetHandle()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->isLoginValid());
        $this->assertSame($this->_auth->getHandle(), $this->_handle);
    }
    
    public function testGetEmail()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->isLoginValid());
        $this->assertSame($this->_auth->getEmail(), $this->_email);
    }
    
    public function testGetMoniker()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->isLoginValid());
        $this->assertSame($this->_auth->getMoniker(), $this->_moniker);
    }
    
    public function testGetUri()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->isLoginValid());
        $this->assertSame($this->_auth->getUri(), $this->_uri);
    }
    
    protected function _fakePostLogin_valid()
    {
        $this->_request->post['submit'] = $this->_auth->locale('SUBMIT_LOGIN');
        $this->_request->post['handle'] = 'pmjones';
        $this->_request->post['passwd'] = 'jones';
    }
    
    protected function _fakePostLogin_badPasswd()
    {
        $this->_request->post['submit'] = $this->_auth->locale('SUBMIT_LOGIN');
        $this->_request->post['handle'] = 'pmjones';
        $this->_request->post['passwd'] = 'badpass';
    }
    
    protected function _fakePostLogin_noSuchUser()
    {
        $this->_request->post['submit'] = $this->_auth->locale('SUBMIT_LOGIN');
        $this->_request->post['handle'] = 'nouser';
        $this->_request->post['passwd'] = 'badpass';
    }
}
?>