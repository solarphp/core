<?php
/**
 * 
 * Abstract adapter class test.
 * 
 */
abstract class Test_Solar_Auth_Login_Adapter extends Solar_Test {
    
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Login_Adapter = array(
    );
    
    /**
     * 
     * The adapter class to instantiate.
     * 
     * @var array
     * 
     */
    protected $_adapter_class;
    
    /**
     * 
     * The adapter instance.
     * 
     * @var array
     * 
     */
    protected $_adapter;
    
    protected $_request;
    
    /**
     * 
     * Sets $_adapter_class based on the test class name.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        
        // Test_Vendor_Foo => Vendor_Foo
        $this->_adapter_class = substr(get_class($this), 5);
    }
    
    /**
     * 
     * Creates an adapter instance.
     * 
     * @return void
     * 
     */
    public function preTest()
    {
        parent::preTest();
        $this->_adapter = Solar::factory(
            $this->_adapter_class,
            $this->_config
        );
        
        $this->_request = Solar_Registry::get('request');
    }
    
    protected function _setLoginRequest()
    {
        
    }
    
    /**
     * 
     * Test -- Constructor.
     * 
     */
    public function test__construct()
    {
        $this->assertInstance($this->_adapter, $this->_adapter_class);
    }
    
    /**
     * 
     * Test -- Loads the user credentials (handle and passwd) from the request source.
     * 
     */
    public function testGetCredentials()
    {
        $this->_setLoginRequest();
        $actual = $this->_adapter->getCredentials();
        
        $expect = array(
            'handle' => 'pmjones',
            'passwd' => 'jones',
        );
        $this->assertSame($actual, $expect);
    }
    
    /**
     * 
     * Test -- Determine the location to redirect to after successful login
     * 
     */
    public function testGetLoginRedirect()
    {
        $this->_setLoginRequest();
        $actual = $this->_adapter->getLoginRedirect();
        $expect = 'http://example.com';
        $this->assertSame($actual, $expect);
    }
    
    /**
     * 
     * Test -- 
     * 
     */
    public function testGetProtocol()
    {
        $actual = $this->_adapter->getProtocol();
        $this->assertInstance($actual, $this->_adapter_class);
    }
    
    /**
     * 
     * Test -- Tells if the current page load appears to be the result of an attempt to log in.
     * 
     */
    public function testIsLoginRequest()
    {
        $this->_setLoginRequest();
        $actual = $this->_adapter->isLoginRequest();
        $this->assertTrue($actual);
    }
    
    public function testIsLoginRequest_not()
    {
        // note that we do not set a login request
        $actual = $this->_adapter->isLoginRequest();
        $this->assertFalse($actual);
    }
    
    /**
     * 
     * Test -- The login was a failure, complete the protocol
     * 
     */
    public function testPostLoginFailure()
    {
        $this->skip('abstract method');
    }
    
    /**
     * 
     * Test -- The login was success, complete the protocol
     * 
     */
    public function testPostLoginSuccess()
    {
        $this->skip('abstract method');
    }
}
