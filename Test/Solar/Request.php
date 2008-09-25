<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Request extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Request = array(
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
        $obj = Solar::factory('Solar_Request');
        $this->assertInstance($obj, 'Solar_Request');
    }
    
    /**
     * 
     * Test -- Retrieves an **unfiltered** value by key from the [[$argv]] property, or an alternate default value if that key does not exist.
     * 
     */
    public function testArgv()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Retrieves an **unfiltered** value by key from the [[$cookie]] property, or an alternate default value if that key does not exist.
     * 
     */
    public function testCookie()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Retrieves an **unfiltered** value by key from the [[$env]] property, or an alternate default value if that key does not exist.
     * 
     */
    public function testEnv()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Retrieves an **unfiltered** value by key from the [[$files]] property, or an alternate default value if that key does not exist.
     * 
     */
    public function testFiles()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Retrieves an **unfiltered** value by key from the [[$get]] property, or an alternate default value if that key does not exist.
     * 
     */
    public function testGet()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Retrieves an **unfiltered** value by key from the [[$http]] property, or an alternate default value if that key does not exist.
     * 
     */
    public function testHttp()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Is this a command-line request?
     * 
     */
    public function testIsCli()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Is this a 'DELETE' request?
     * 
     */
    public function testIsDelete()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Is this a 'GET' request?
     * 
     */
    public function testIsGet()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Is this a 'POST' request?
     * 
     */
    public function testIsPost()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Is this a 'PUT' request?
     * 
     */
    public function testIsPut()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Is this an XmlHttpRequest?
     * 
     */
    public function testIsXhr()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Retrieves an **unfiltered** value by key from the [[$post]] property, or an alternate default value if that key does not exist.
     * 
     */
    public function testPost()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Retrieves an **unfiltered** value by key from the [[$post]] *and*  [[$files]] properties, or an alternate default value if that key does  not exist in either location.
     * 
     */
    public function testPostAndFiles()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Reloads properties from the superglobal arrays.
     * 
     */
    public function testReset()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Retrieves an **unfiltered** value by key from the [[$server]] property, or an alternate default value if that key does not exist.
     * 
     */
    public function testServer()
    {
        $this->todo('stub');
    }
}
