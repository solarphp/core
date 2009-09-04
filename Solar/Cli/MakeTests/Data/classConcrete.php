/**
 * 
 * Concrete class test.
 * 
 */
class Test_{:class} extends {:extends} {
    
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
     * A hook that activates before _buildConfig() in the constructor.
     * 
     * Allows you to modify the object before configuration is built; for
     * example, to set properties or to check for extensions.
     * 
     * @return void
     * 
     */
    protected function _preConfig()
    {
        parent::_preConfig();
    }
    
    /**
     * 
     * A hook that activates after _buildConfig() in the constructor.
     * 
     * Allows you to modify $this->_config after it has been built.
     * 
     * @return void
     * 
     */
    protected function _postConfig()
    {
        parent::_postConfig();
    }
    
    /**
     * 
     * A hook that activates at the end of the constructor.
     * 
     * Allows you to modify the object properties after config has been built,
     * and to call follow-on methods.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
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
