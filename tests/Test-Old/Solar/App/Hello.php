<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_App_Hello extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_App_Hello = array(
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
        $obj = Solar::factory('Solar_App_Hello');
        $this->assertInstance($obj, 'Solar_App_Hello');
    }
    
    /**
     * 
     * Test -- Try to force users to define what their view variables are.
     * 
     */
    public function test__get()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Try to force users to define what their view variables are.
     * 
     */
    public function test__set()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Shows a generic error page.
     * 
     */
    public function testActionError()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Resets to the requested locale code and shows translated output as an HTML file.
     * 
     */
    public function testActionMain()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Resets to the requested locale code and shows translated output as an RSS file.
     * 
     */
    public function testActionRss()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Executes the requested action and displays its output.
     * 
     */
    public function testDisplay()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Executes the requested action and returns its output with layout.
     * 
     */
    public function testFetch()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Injects the front-controller object that invoked this page-controller.
     * 
     */
    public function testSetFrontController()
    {
        $this->todo('stub');
    }


}
