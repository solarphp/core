<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar = array(
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
        $obj = Solar::factory('Solar');
        $this->assertInstance($obj, 'Solar');
    }
    
    /**
     * 
     * Test -- Cleans the global scope of all variables that are found in other super-globals.
     * 
     */
    public function testCleanGlobals()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Safely gets a configuration group array or element value.
     * 
     */
    public function testConfig()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns a dependency object.
     * 
     */
    public function testDependency()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Convenience method for dirname() and higher-level directories.
     * 
     */
    public function testDirname()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Generates a simple exception, but does not throw it.
     * 
     */
    public function testException()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Convenience method to instantiate and configure an object.
     * 
     */
    public function testFactory()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Fetches config file values.
     * 
     */
    public function testFetchConfig()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Hack for [[php::file_exists() | ]] that checks the include_path.
     * 
     */
    public function testFileExists()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- "Fixes" a directory string for the operating system.
     * 
     */
    public function testFixdir()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Hack for [[php::is_dir() | ]] that checks the include_path.
     * 
     */
    public function testIsDir()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Check to see if an object name already exists in the registry.
     * 
     */
    public function testIsRegistered()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Loads a class file from the include_path.
     * 
     */
    public function testLoadClass()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Loads an interface file from the include_path.
     * 
     */
    public function testLoadInterface()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns an array of the parent classes for a given class.
     * 
     */
    public function testParents()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Registers an object under a unique name.
     * 
     */
    public function testRegister()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Accesses an object in the registry.
     * 
     */
    public function testRegistry()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Uses [[php::include() | ]] to run a script in a limited scope.
     * 
     */
    public function testRun()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Starts Solar: loads configuration values and and sets up the environment.
     * 
     */
    public function testStart()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Stops Solar: runs stop scripts and cleans up the Solar environment.
     * 
     */
    public function testStop()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the OS-specific directory for temporary files, optionally with a path added to it.
     * 
     */
    public function testTemp()
    {
        $this->todo('stub');
    }

    
    /**
     * 
     * Test -- Loads a class or interface file from the include_path.
     * 
     */
    public function testAutoload()
    {
        $this->todo('stub');
    }

}
