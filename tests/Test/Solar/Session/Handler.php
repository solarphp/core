<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Session_Handler extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Session_Handler = array(
    );
    
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
        $obj = Solar::factory('Solar_Session_Handler');
        $this->assertInstance($obj, 'Solar_Session_Handler_Adapter');
    }
    
    /**
     * 
     * Test -- Disallow all calls to methods besides factory() and the existing support methods.
     * 
     */
    public function test__call()
    {
        $obj = new Solar_Session_Handler();
        try {
            $obj->noSuchMethod();
            $this->fail('__call() should not work');
        } catch (Exception $e) {
            $this->assertTrue(true);
        }
    }
    
    /**
     * 
     * Test -- Factory method for returning adapter objects.
     * 
     */
    public function testFactory()
    {
        // Test_Solar_Foo => Solar_Foo_Adapter
        $expect = substr(get_class($this), 5) . '_Adapter';
        
        $actual = Solar::factory('Solar_Session_Handler');
        $this->assertInstance($actual, $expect);
    }
}
