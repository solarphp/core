<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_View_Helper_JsPrototype_Ajax extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_View_Helper_JsPrototype_Ajax = array(
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
        $obj = Solar::factory('Solar_View_Helper_JsPrototype_Ajax');
        $this->assertInstance($obj, 'Solar_View_Helper_JsPrototype_Ajax');
    }
    
    /**
     * 
     * Test -- Method interface.
     * 
     */
    public function testAjax()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Note that this method is completely unused by this class.
     * 
     */
    public function testFetch()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns options keys whose values should be dequoted, as the values are expected to be `function() {...}` or names of pre-defined functions elsewhere in the JavaScript environment.
     * 
     */
    public function testGetFunctionKeys()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Method interface.
     * 
     */
    public function testJsLibrary()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Method interface.
     * 
     */
    public function testJsPrototype()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Creates a string of JavaScript which, when executed, will create one instance of Prototype's Ajax.Request object.
     * 
     */
    public function testRequest()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Use this method when the requested URL returns HTML that you want to inject directly into a specific element on the page.
     * 
     */
    public function testUpdater()
    {
        $this->todo('stub');
    }


}
