<?php

abstract class Test_Solar_Auth_Adapter extends Solar_Test {
    
    protected $_auth;
    
    protected $_class;
    
    protected $_config = array();
    
    protected $_post;
    
    protected $_handle = 'pmjones';
    
    protected $_email = null;
    
    protected $_moniker = null;
    
    protected $_uri = null;
    
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    public function __destruct()
    {
        parent::__destruct();
    }
    
    public function setup()
    {
        // convert from Test_Solar_Adapter_Whatever
        // to Solar_Adapter_Whatever
        $this->_class = substr(get_class($this), 5);
        $this->_auth = Solar::factory($this->_class, $this->_config);
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
        $prior_post = $_POST;
        $_POST['submit'] = $this->_auth->locale('SUBMIT_LOGIN');
        
        // assert
        $this->assertTrue($this->_auth->isLoginRequest());
        
        // restore POST params
        $_POST = $prior_post;
    }
    
    public function testIsLoginRequest_false()
    {
        $this->assertFalse($this->_auth->isLoginRequest());
    }
    
    public function testIsLogoutRequest_true()
    {
        // fake the POST parameters
        $prior_post = $_POST;
        $_POST['submit'] = $this->_auth->locale('SUBMIT_LOGOUT');
        
        // assert
        $this->assertTrue($this->_auth->isLogoutRequest());
        
        // restore POST params
        $_POST = $prior_post;
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
        $this->_restorePost();
    }
    
    public function testIsLoginValid_badPasswd()
    {
        $this->_fakePostLogin_badPasswd();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->isLoginValid());
        $this->_restorePost();
    }
    
    public function testIsLoginValid_noSuchUser()
    {
        $this->_fakePostLogin_noSuchUser();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->isLoginValid());
        $this->_restorePost();
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
        $this->_restorePost();
    }
    
    public function testGetEmail()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->isLoginValid());
        $this->assertSame($this->_auth->getEmail(), $this->_email);
        $this->_restorePost();
    }
    
    public function testGetMoniker()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->isLoginValid());
        $this->assertSame($this->_auth->getMoniker(), $this->_moniker);
        $this->_restorePost();
    }
    
    public function testGetUri()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->isLoginValid());
        $this->assertSame($this->_auth->getUri(), $this->_uri);
        $this->_restorePost();
    }
    
    protected function _fakePostLogin_valid()
    {
        $this->_post = $_POST;
        $_POST['submit'] = $this->_auth->locale('SUBMIT_LOGIN');
        $_POST['handle'] = 'pmjones';
        $_POST['passwd'] = 'jones';
    }
    
    protected function _fakePostLogin_badPasswd()
    {
        $this->_post = $_POST;
        $_POST['submit'] = $this->_auth->locale('SUBMIT_LOGIN');
        $_POST['handle'] = 'pmjones';
        $_POST['passwd'] = 'badpass';
    }
    
    protected function _fakePostLogin_noSuchUser()
    {
        $this->_post = $_POST;
        $_POST['submit'] = $this->_auth->locale('SUBMIT_LOGIN');
        $_POST['handle'] = 'nouser';
        $_POST['passwd'] = 'badpass';
    }
    
    protected function _restorePost()
    {
        $_POST = $this->_post;
    }
}
?>