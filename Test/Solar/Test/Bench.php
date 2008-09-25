<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Test_Bench extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Test_Bench = array(
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
        $obj = Solar::factory('Solar_Test_Bench');
        $this->assertInstance($obj, 'Solar_Test_Bench');
    }
    
    /**
     * 
     * Test -- Runs each benchmark method for a certain number of loops.
     * 
     */
    public function testLoop()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Executes this code before running any benchmarks.
     * 
     */
    public function testSetup()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Executes this code after running all benchmarks.
     * 
     */
    public function testTeardown()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Runs each benchmark method for a certain number of minutes.
     * 
     */
    public function testTime()
    {
        $this->todo('stub');
    }
}
