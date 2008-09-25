<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_View extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_View = array(
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
        $obj = Solar::factory('Solar_View');
        $this->assertInstance($obj, 'Solar_View');
    }
    
    /**
     * 
     * Test -- Executes a helper method with arbitrary parameters.
     * 
     */
    public function test__call()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Disallows setting of underscore-prefixed variables.
     * 
     */
    public function test__set()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Add to the helper class stack.
     * 
     */
    public function testAddHelperClass()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Add to the template directory path stack.
     * 
     */
    public function testAddTemplatePath()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets variables for the view.
     * 
     */
    public function testAssign()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Displays a template directly.
     * 
     */
    public function testDisplay()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Built-in helper for escaping output.
     * 
     */
    public function testEscape()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Fetches template output.
     * 
     */
    public function testFetch()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns an internal helper object; creates it as needed.
     * 
     */
    public function testGetHelper()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the helper class stack.
     * 
     */
    public function testGetHelperClass()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the template directory path stack.
     * 
     */
    public function testGetTemplatePath()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Creates a new standalone helper object.
     * 
     */
    public function testNewHelper()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Executes a partial template in its own scope, optionally with  variables into its within its scope.
     * 
     */
    public function testPartial()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Reset the helper class stack.
     * 
     */
    public function testSetHelperClass()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Reset the template directory path stack.
     * 
     */
    public function testSetTemplatePath()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns the path to the requested template script.
     * 
     */
    public function testTemplate()
    {
        $this->todo('stub');
    }
}
