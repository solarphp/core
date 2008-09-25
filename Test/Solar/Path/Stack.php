<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Path_Stack extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Path_Stack = array(
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
        $obj = Solar::factory('Solar_Path_Stack');
        $this->assertInstance($obj, 'Solar_Path_Stack');
    }
    
    /**
     * 
     * Test -- Adds one or more directories to the stack.
     * 
     */
    public function testAdd()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Finds a file in the path stack.
     * 
     */
    public function testFind()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Finds a file in the path stack using realpath().
     * 
     */
    public function testFindReal()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Gets a copy of the current stack.
     * 
     */
    public function testGet()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Clears the stack and adds one or more directories.
     * 
     */
    public function testSet()
    {
        $this->todo('stub');
    }
}
