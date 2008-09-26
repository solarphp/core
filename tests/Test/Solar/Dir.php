<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Dir extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Dir = array(
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
        $obj = Solar::factory('Solar_Dir');
        $this->assertInstance($obj, 'Solar_Dir');
    }
    
    /**
     * 
     * Test -- Hack for [[php::is_dir() | ]] that checks the include_path.
     * 
     */
    public function testExists()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- "Fixes" a directory string for the operating system.
     * 
     */
    public function testFix()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Replacement for mkdir() to supress warnings and throw exceptions in  their place.
     * 
     */
    public function testMkdir()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Convenience method for dirname() and higher-level directories.
     * 
     */
    public function testName()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Replacement for rmdir() to supress warnings and throw exceptions in  their place.
     * 
     */
    public function testRmdir()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the OS-specific directory for temporary files.
     * 
     */
    public function testTmp()
    {
        $this->todo('stub');
    }
}
