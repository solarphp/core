<?php
/**
 * Parent test.
 */
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Adapter.php';

/**
 * 
 * Adapter class test.
 * 
 */
class Test_Solar_Cache_Adapter_Var extends Test_Solar_Cache_Adapter {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Cache_Adapter_Var = array(
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
        $this->todo('need adapter-specific config');
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
        $obj = Solar::factory('Solar_Cache_Adapter_Var');
        $this->assertInstance($obj, 'Solar_Cache_Adapter_Var');
    }
    
    /**
     * 
     * Test -- Inserts cache entry data, but only if the entry does not already exist.
     * 
     */
    public function testAdd()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Deletes a cache entry.
     * 
     */
    public function testDelete()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Removes all cache entries.
     * 
     */
    public function testDeleteAll()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the name for the entry key.
     * 
     */
    public function testEntry()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Gets cache entry data.
     * 
     */
    public function testFetch()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Gets the cache lifetime in seconds.
     * 
     */
    public function testGetLife()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Increments a cache entry value by the specified amount.
     * 
     */
    public function testIncrement()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Gets the current activity state of the cache (on or off).
     * 
     */
    public function testIsActive()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets cache entry data.
     * 
     */
    public function testSave()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Makes the cache active (true) or inactive (false).
     * 
     */
    public function testSetActive()
    {
        $this->todo('stub');
    }
}
