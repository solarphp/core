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
class Test_Solar_Access_Adapter_Open extends Test_Solar_Access_Adapter {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Access_Adapter_Open = array(
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
        $obj = Solar::factory('Solar_Access_Adapter_Open');
        $this->assertInstance($obj, 'Solar_Access_Adapter_Open');
    }
    
    /**
     * 
     * Test -- Fetch access privileges for a user handle and roles.
     * 
     */
    public function testFetch()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Tells whether or not to allow access to a class/action/process combination.
     * 
     */
    public function testIsAllowed()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Checks to see if the current user is the owner of application-specific content; always returns true, to allow for programmatic owner checks.
     * 
     */
    public function testIsOwner()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Fetches the access list from the adapter into $this->list.
     * 
     */
    public function testLoad()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Resets the current access controls to a blank array, along with the  $_auth and $_role properties.
     * 
     */
    public function testReset()
    {
        $this->todo('stub');
    }
}
