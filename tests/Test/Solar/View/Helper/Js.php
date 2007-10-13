<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_View_Helper_Js extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_View_Helper_Js = array(
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
        $obj = Solar::factory('Solar_View_Helper_Js');
        $this->assertInstance($obj, 'Solar_View_Helper_Js');
    }
    
    /**
     * 
     * Test -- Add the specified JavaScript file to the Helper_Js file list if it's not already present.
     * 
     */
    public function testAddFile()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Add the script defined in $src to the inline scripts array.
     * 
     */
    public function testAddScriptInline()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Add the specified CSS file to the Helper_Js styles list if it's not already present.
     * 
     */
    public function testAddStyle()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Build and return JavaScript for page header.
     * 
     */
    public function testFetchFiles()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Returns all defined inline scripts.
     * 
     */
    public function testFetchInline()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Build and return list of CSS files for page header.
     * 
     */
    public function testFetchStyles()
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
     * Test -- Fluent interface.
     * 
     */
    public function testJs()
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
     * Test -- Resets the helper entirely.
     * 
     */
    public function testReset()
    {
        $this->todo('stub');
    }


}
