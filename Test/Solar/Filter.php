<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Filter extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Filter = array(
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
        $obj = Solar::factory('Solar_Filter');
        $this->assertInstance($obj, 'Solar_Filter');
    }
    
    /**
     * 
     * Test -- Magic call to filter methods represented as classes.
     * 
     */
    public function test__call()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Adds one filter-chain method for a data key.
     * 
     */
    public function testAddChainFilter()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Adds many filter-chain methods for a data key.
     * 
     */
    public function testAddChainFilters()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Add to the filter class stack.
     * 
     */
    public function testAddFilterClass()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Applies the filter chain to an array of data in-place.
     * 
     */
    public function testApplyChain()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Call this method before you unset() this instance to fully recover memory from circular-referenced objects.
     * 
     */
    public function testFree()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Gets the list of invalid keys and feedback messages from the filter chain.
     * 
     */
    public function testGetChainInvalid()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Gets a copy of the data array, or a specific element of data, being processed by [[applyChain()]].
     * 
     */
    public function testGetData()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Gets the current data key being processed by the filter chain.
     * 
     */
    public function testGetDataKey()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Gets the stored filter object by method name.
     * 
     */
    public function testGetFilter()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the filter class stack.
     * 
     */
    public function testGetFilterClass()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the value of the 'require' flag.
     * 
     */
    public function testGetRequire()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Creates a new filter object by method name.
     * 
     */
    public function testNewFilter()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Resets the filter chain and required keys.
     * 
     */
    public function testResetChain()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the object used for getting locale() translations during [[applyChain()]].
     * 
     */
    public function testSetChainLocaleObject()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets whether or not a particular data key is required to be present and non-blank in the data being processed by [[applyChain()]].
     * 
     */
    public function testSetChainRequire()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets one data element being processed by [[applyChain()]].
     * 
     */
    public function testSetData()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Reset the filter class stack.
     * 
     */
    public function testSetFilterClass()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the value of the 'require' flag.
     * 
     */
    public function testSetRequire()
    {
        $this->todo('stub');
    }
}
