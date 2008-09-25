<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Uri_Action extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Uri_Action = array(
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
        $obj = Solar::factory('Solar_Uri_Action');
        $this->assertInstance($obj, 'Solar_Uri_Action');
    }
    
    /**
     * 
     * Test -- Implements access to $_query **by reference** so that it appears to be  a public $query property.
     * 
     */
    public function test__get()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Implements the virtual $query property.
     * 
     */
    public function test__set()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns a URI based on the object properties.
     * 
     */
    public function testGet()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the query portion as a string.
     * 
     */
    public function testGetQuery()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns a URI based on the specified string.
     * 
     */
    public function testQuick()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets properties from a specified URI.
     * 
     */
    public function testSet()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the Solar_Uri::$path array from a string.
     * 
     */
    public function testSetPath()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the query string in the URI, for Solar_Uri::getQuery() and Solar_Uri::$query.
     * 
     */
    public function testSetQuery()
    {
        $this->todo('stub');
    }
}
