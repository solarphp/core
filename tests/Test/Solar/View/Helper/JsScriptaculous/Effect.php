<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_View_Helper_JsScriptaculous_Effect extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_View_Helper_JsScriptaculous_Effect = array(
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
        $obj = Solar::factory('Solar_View_Helper_JsScriptaculous_Effect');
        $this->assertInstance($obj, 'Solar_View_Helper_JsScriptaculous_Effect');
    }
    
    /**
     * 
     * Test -- Overload method for core script.aculo.us effects that follow the same convention, which include ...
     * 
     */
    public function test__call()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Method interface.
     * 
     */
    public function testEffect()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Fetch method called by Solar_View_Helper_Js.
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
    public function testJsScriptaculous()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Setup trigger for core script.aculo.us MoveBy effect.
     * 
     */
    public function testMoveBy()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Setup trigger for core script.aculo.us Scale effect.
     * 
     */
    public function testScale()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Setup trigger for combination Toggle utility method.
     * 
     */
    public function testToggle()
    {
        $this->todo('stub');
    }


}
