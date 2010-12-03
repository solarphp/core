<?php
/**
 * 
 * Abstract adapter class test.
 * 
 */
abstract class Test_Solar_Auth_Logout_Adapter extends Solar_Test {
    
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Logout_Adapter = array(
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
    
    protected function _setLogoutRequest()
    {
        
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
        
        Solar_Registry::set('session_manager', 'Solar_Session_Manager');
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
     * Test -- Determine the location to redirect to after logout
     * 
     */
    public function testGetLogoutRedirect()
    {
        $this->skip('abstract method');
    }
    
    /**
     * 
     * Test -- 
     * 
     */
    public function testGetProtocol()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Tells if the current page load appears to be the result of an attempt to log out.
     * 
     */
    public function testIsLogoutRequest()
    {
        $this->_setLogoutRequest();
        $actual = $this->_adapter->isLogoutRequest();
        $this->assertTrue($actual);
    }
    
    public function testIsLogoutRequest_not()
    {
        $actual = $this->_adapter->isLogoutRequest();
        $this->assertFalse($actual);
    }
}
