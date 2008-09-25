<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Class_Stack extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Class_Stack = array(
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
    public function __construct($config)
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
        $obj = Solar::factory('Solar_Class_Stack');
        $this->assertInstance($obj, 'Solar_Class_Stack');
    }
    
    /**
     * 
     * Test -- Adds one or more classes to the stack.
     * 
     */
    public function testAdd()
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
     * Test -- Loads a class using the class stack prefixes.
     * 
     */
    public function testLoad()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Clears the stack and adds one or more classes.
     * 
     */
    public function testSet()
    {
        $this->todo('stub');
    }


}
