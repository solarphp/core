<?php

require_once dirname(__FILE__) . '/../../SolarUnitTest.config.php';

abstract class Solar_Auth_AdapterTestCase extends PHPUnit_Framework_TestCase
{
    
    protected $_auth;
    
    protected $_class;
    
    protected $_config = array();
    
    protected $_post;
    
    protected $_handle = 'pmjones';
    
    protected $_email = null;
    
    protected $_moniker = null;
    
    protected $_uri = null;
    
    protected $_request;
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // convert from Solar_Auth_Adapter_WhateverTest
        // to Solar_Auth_Adapter_Whatever
        $this->_class = substr(get_class($this), 0, -4);
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
        $this->assertType($this->_class, $this->_auth);
    }
    
    public function testIsLoginRequest_true()
    {
        // fake the POST parameters
        $this->_request->post['process'] = $this->_auth->locale('PROCESS_LOGIN');
        $this->assertTrue($this->_auth->isLoginRequest());
    }
    
    public function testIsLoginRequest_false()
    {
        $this->assertFalse($this->_auth->isLoginRequest());
    }
    
    public function testIsLogoutRequest_true()
    {
        // fake the POST parameters
        $this->_request->post['process'] = $this->_auth->locale('PROCESS_LOGOUT');
        $this->assertTrue($this->_auth->isLogoutRequest());
    }
    
    public function testIsLogoutRequest_false()
    {
        $this->assertFalse($this->_auth->isLogoutRequest());
    }
    
    public function testProcessLogin_true()
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
    
    public function testReset()
    {
        $this->_auth->reset();
        $this->assertNull($this->_auth->handle);
        $this->assertNull($this->_auth->email);
        $this->assertNull($this->_auth->moniker);
        $this->assertNull($this->_auth->uri);
        $this->assertNull($this->_auth->uid);
    }
    
    public function testGetHandle()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->processLogin());
        $this->assertSame($this->_auth->handle, $this->_handle);
    }
    
    public function testGetEmail()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->processLogin());
        $this->assertSame($this->_auth->email, $this->_email);
    }
    
    public function testGetMoniker()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->processLogin());
        $this->assertSame($this->_auth->moniker, $this->_moniker);
    }
    
    public function testGetUri()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertTrue($this->_auth->processLogin());
        $this->assertSame($this->_auth->uri, $this->_uri);
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
}
?>