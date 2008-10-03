<?php
/**
 * 
 * Adapter class test.
 * 
 */
class Test_Solar_Auth_Adapter_None extends Test_Solar_Auth_Adapter {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Adapter_None = array(
    );
    
    // no such thing as a valid login with the 'none' adapter
    
    public function setup()
    {
        $this->_handle = null;
        parent::setup();
    }
    
    public function testProcessLogin()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->processLogin());
    }
    
    public function test_handle()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->processLogin());
        $this->assertNull($this->_auth->handle);
    }
    
    public function test_email()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->processLogin());
        $this->assertNull($this->_auth->email);
    }
    
    public function test_moniker()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->processLogin());
        $this->assertNull($this->_auth->moniker);
    }
    
    public function test_uri()
    {
        $this->_fakePostLogin_valid();
        $this->assertTrue($this->_auth->isLoginRequest());
        $this->assertFalse($this->_auth->processLogin());
        $this->assertNull($this->_auth->email);
    }
}
