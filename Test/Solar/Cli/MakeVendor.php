<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Cli_MakeVendor extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Cli_MakeVendor = array(
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
        $obj = Solar::factory('Solar_Cli_MakeVendor');
        $this->assertInstance($obj, 'Solar_Cli_MakeVendor');
    }
    
    /**
     * 
     * Test -- Public interface to execute the command.
     * 
     */
    public function testExec()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the help text for this command.
     * 
     */
    public function testGetInfoHelp()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns an array of option flags and descriptions for this command.
     * 
     */
    public function testGetInfoOptions()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Injects the console-controller object (if any) that invoked this command.
     * 
     */
    public function testSetConsoleController()
    {
        $this->todo('stub');
    }
}
