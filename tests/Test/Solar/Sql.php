<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Sql extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Sql = array(
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
        $obj = Solar::factory('Solar_Sql');
        $this->assertInstance($obj, 'Solar_Sql_Adapter');
    }
    
    /**
     * 
     * Test -- Disallow all calls to methods besides factory() and the existing support methods.
     * 
     */
    public function test__call()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Factory method for returning adapter objects.
     * 
     */
    public function testFactory()
    {
        $this->todo('stub');
    }
}
