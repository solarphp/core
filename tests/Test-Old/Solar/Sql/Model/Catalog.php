<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Sql_Model_Catalog extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Sql_Model_Catalog = array(
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
        $obj = Solar::factory('Solar_Sql_Model_Catalog');
        $this->assertInstance($obj, 'Solar_Sql_Model_Catalog');
    }
    
    /**
     * 
     * Test -- Checks to see if catalog data exists for a model class.
     * 
     */
    public function testExists()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Gets the catalog data for a model class.
     * 
     */
    public function testGet()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the catalog data for a model class.
     * 
     */
    public function testSet()
    {
        $this->todo('stub');
    }

    
    /**
     * 
     * Test -- Resets (removes) all the catalog data, or resets (removes) just the catalog data for one class.
     * 
     */
    public function testReset()
    {
        $this->todo('stub');
    }





}
