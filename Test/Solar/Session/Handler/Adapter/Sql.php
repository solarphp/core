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
class Test_Solar_Session_Handler_Adapter_Sql extends Test_Solar_Session_Handler_Adapter {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Session_Handler_Adapter_Sql = array(
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
        $obj = Solar::factory('Solar_Session_Handler_Adapter_Sql');
        $this->assertInstance($obj, 'Solar_Session_Handler_Adapter_Sql');
    }
    
    /**
     * 
     * Test -- Close session handler.
     * 
     */
    public function testClose()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Destroys session data.
     * 
     */
    public function testDestroy()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Removes old session data (garbage collection).
     * 
     */
    public function testGc()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Open session handler.
     * 
     */
    public function testOpen()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Reads session data.
     * 
     */
    public function testRead()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Writes session data.
     * 
     */
    public function testWrite()
    {
        $this->todo('stub');
    }
}
