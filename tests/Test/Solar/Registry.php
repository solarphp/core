<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Registry extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Registry = array(
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
        $this->skip("construction disallowed, static only");
    }
    
    /**
     * 
     * Test -- Check to see if an object name already exists in the registry.
     * 
     */
    public function testExists()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Accesses an object in the registry.
     * 
     */
    public function testGet()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Registers an object under a unique name.
     * 
     */
    public function testSet()
    {
        $this->todo('stub');
    }
}
