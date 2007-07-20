<?php

require_once dirname(dirname(__FILE__)) . '/AdapterTestCase.php';

class Solar_Auth_Adapter_NoneTest extends Solar_Auth_AdapterTestCase {
    
    public function setup()
    {
        $this->_handle = null;
        parent::setup();
    }
    
    // no such thing as a valid login with the 'none' adapter
    
    public function testProcessLogin_true()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->processLogin());
    }
    
    public function testGetHandle()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->processLogin());
        $this->assertNull($this->_auth->handle);
    }
    
    public function testGetEmail()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->processLogin());
        $this->assertNull($this->_auth->email);
    }
    
    public function testGetMoniker()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->processLogin());
        $this->assertNull($this->_auth->moniker);
    }
    
    public function testGetUri()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->processLogin());
        $this->assertNull($this->_auth->email);
    }
}