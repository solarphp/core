<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Markdown_Plugin_Html extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Markdown_Plugin_Html = array(
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
        $obj = Solar::factory('Solar_Markdown_Plugin_Html');
        $this->assertInstance($obj, 'Solar_Markdown_Plugin_Html');
    }
    
    /**
     * 
     * Test -- When cleaning up after parsing, replace all HTML tokens with their saved blocks.
     * 
     */
    public function testCleanup()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Get the list of characters this plugin uses for parsing.
     * 
     */
    public function testGetChars()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Is this a block-level plugin?
     * 
     */
    public function testIsBlock()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Run this plugin during the "cleanup" phase?
     * 
     */
    public function testIsCleanup()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Run this plugin during the "prepare" phase?
     * 
     */
    public function testIsPrepare()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Is this a span-level plugin?
     * 
     */
    public function testIsSpan()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Removes HTML blocks and replaces with delimited tokens.
     * 
     */
    public function testParse()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- When preparing text for parsing, remove pre-existing HTML blocks.
     * 
     */
    public function testPrepare()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Resets this plugin to its original state (for multiple parsings).
     * 
     */
    public function testReset()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the "parent" Markdown object.
     * 
     */
    public function testSetMarkdown()
    {
        $this->todo('stub');
    }
}
