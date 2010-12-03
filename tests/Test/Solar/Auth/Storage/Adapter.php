<?php
/**
 * 
 * Abstract adapter class test.
 * 
 */
abstract class Test_Solar_Auth_Storage_Adapter extends Solar_Test {
    
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Storage_Adapter = array(
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
    
    protected $_expect;
    
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
     * Test -- Verifies set of credentials.
     * 
     */
    public function testValidateCredentials()
    {
        $actual = $this->_adapter->validateCredentials(array(
            'handle' => 'pmjones',
            'passwd' => 'jones',
        ));
        
        Solar::dump($actual);
        
        $this->assertSame($actual, $this->_expect);
    }
    
    public function testValidateCredentials_badPasswd()
    {
        $actual = $this->_adapter->validateCredentials(array(
            'handle' => 'pmjones',
            'passwd' => 'wrongpassword',
        ));
        
        $this->assertFalse($actual);
    }
    
    public function testValidateCredentials_badHandle()
    {
        $actual = $this->_adapter->validateCredentials(array(
            'handle' => 'nosuchuser',
            'passwd' => 'anypassword',
        ));
        
        $this->assertFalse($actual);
    }
}
