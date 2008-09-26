<?php
/**
 * 
 * Abstract class test.
 * 
 */
class Test_Solar_Role_Adapter extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Role_Adapter = array(
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
        $obj = Solar::factory('Solar_Role_Adapter');
        $this->assertInstance($obj, 'Solar_Role_Adapter');
    }
    
    /**
     * 
     * Test -- Provides magic "isRoleName()" to map to "is('role_name')".
     * 
     */
    public function test__call()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Adapter-specific method to find roles for loading.
     * 
     */
    public function testFetch()
    {
        $this->skip('abstract method');
    }
    
    /**
     * 
     * Test -- Check to see if a user is in a role.
     * 
     */
    public function testIs()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Check to see if a user is in all of the listed roles.
     * 
     */
    public function testIsAll()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Check to see if a user is in any of the listed roles.
     * 
     */
    public function testIsAny()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Load the list of roles for the given user from the adapter.
     * 
     */
    public function testLoad()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Resets the role list to nothing.
     * 
     */
    public function testReset()
    {
        $this->todo('stub');
    }
}
