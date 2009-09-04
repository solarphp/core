/**
 * 
 * Abstract class test.
 * 
 */
abstract class Test_{:class} extends {:extends} {
    
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_{:class} = array(
    );
    
    // -----------------------------------------------------------------
    // 
    // Support methods.
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Skips the test because this is an abstract class.
     * 
     * @return void
     * 
     */
    protected function _preConfig()
    {
        $this->skip('abstract class');
    }
    
    /**
     * 
     * Destructor; runs after all methods are complete.
     * 
     * @param array $config Configuration value overrides, if any.
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
     * Teardown; runs after each test method.
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
        $obj = Solar::factory('{:class}');
        $this->assertInstance($obj, '{:class}');
    }
}
