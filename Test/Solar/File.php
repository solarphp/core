<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_File extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_File = array(
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
        $obj = Solar::factory('Solar_File');
        $this->assertInstance($obj, 'Solar_File');
    }
    
    /**
     * 
     * Test -- Hack for [[php::file_exists() | ]] that checks the include_path.
     * 
     */
    public function testExists()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Uses [[php::include() | ]] to run a script in a limited scope.
     * 
     */
    public function testLoad()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the OS-specific directory for temporary files, with a file name appended.
     * 
     */
    public function testTmp()
    {
        $this->todo('stub');
    }
}
