<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Example extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Example = array(
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
        $obj = Solar::factory('Solar_Example');
        $this->assertInstance($obj, 'Solar_Example');
    }
    
    /**
     * 
     * Test -- Throws ERR_GENERIC_CONDITION for this class.
     * 
     */
    public function testClassGenericException()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Throws ERR_CUSTOM_CONDITION for this class.
     * 
     */
    public function testClassSpecificException()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Throws a user-specified error code for this class.
     * 
     */
    public function testExceptionFromCode()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Used for testing Solar_Filter::callback() as an instance method.
     * 
     */
    public function testFilterCallback()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Throws ERR_NO_SUCH_CONDITION for this class.
     * 
     */
    public function testSolarGenericException()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Throws ERR_FILE_NOT_FOUND for this class.
     * 
     */
    public function testSolarSpecificException()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Used for testing Solar_Filter::callback() as a static method.
     * 
     */
    public function testStaticFilterCallback()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Used for testing Solar_Valid::callback() as a static method.
     * 
     */
    public function testStaticValidIsInt()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Used for testing Solar_Valid::callback() as an instance method.
     * 
     */
    public function testValidIsInt()
    {
        $this->todo('stub');
    }
}
