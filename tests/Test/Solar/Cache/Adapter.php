<?php
/**
 * 
 * Abstract class test.
 * 
 */
class Test_Solar_Cache_Adapter extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Cache_Adapter = array(
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
        $this->skip('abstract class');
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
        $obj = Solar::factory('Solar_Cache_Adapter');
        $this->assertInstance($obj, 'Solar_Cache_Adapter');
    }
    
    /**
     * 
     * Test -- Inserts cache entry data *only if it does not already exist*.
     * 
     */
    public function testAdd()
    {
        $this->skip('abstract method');
    }
    
    /**
     * 
     * Test -- Deletes a cache entry.
     * 
     */
    public function testDelete()
    {
        $this->skip('abstract method');
    }
    
    /**
     * 
     * Test -- Deletes all entries from the cache.
     * 
     */
    public function testDeleteAll()
    {
        $this->skip('abstract method');
    }
    
    /**
     * 
     * Test -- Returns the adapter-specific name for the entry key.
     * 
     */
    public function testEntry()
    {
        $this->skip('abstract method');
    }
    
    /**
     * 
     * Test -- Gets cache entry data.
     * 
     */
    public function testFetch()
    {
        $this->skip('abstract method');
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
        $this->skip('abstract method');
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
     * Test -- Updates cache entry data, inserting if it does not already exist.
     * 
     */
    public function testSave()
    {
        $this->skip('abstract method');
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
