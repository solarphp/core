<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Debug_Timer extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Debug_Timer = array(
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
        $obj = Solar::factory('Solar_Debug_Timer');
        $this->assertInstance($obj, 'Solar_Debug_Timer');
    }
    
    /**
     * 
     * Test -- Displays formatted output of the current profile.
     * 
     */
    public function testDisplay()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Fetches the current profile formatted as a table.
     * 
     */
    public function testFetch()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Marks the time.
     * 
     */
    public function testMark()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns profiling information as an array.
     * 
     */
    public function testProfile()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Resets the profile and marks a new starting time.
     * 
     */
    public function testStart()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Stops the timer.
     * 
     */
    public function testStop()
    {
        $this->todo('stub');
    }
}
