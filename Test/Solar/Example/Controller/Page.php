<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Example_Controller_Page extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Example_Controller_Page = array(
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
        $obj = Solar::factory('Solar_Example_Controller_Page');
        $this->assertInstance($obj, 'Solar_Example_Controller_Page');
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
     * Test -- Action named in BumpyCase.
     * 
     */
    public function testActionBumpyCase()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Default action.
     * 
     */
    public function testActionFoo()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- An action method that has no related view script.
     * 
     */
    public function testActionNoRelatedView()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Tests the _forward() method.
     * 
     */
    public function testActionTestForward()
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
     * Test -- Sets the default action for testing.
     * 
     */
    public function testSetActionDefault()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the name for this page-controller; generally used only by the  front-controller when static routing leads to this page.
     * 
     */
    public function testSetController()
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
