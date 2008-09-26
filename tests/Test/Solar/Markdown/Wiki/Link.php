<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Markdown_Wiki_Link extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Markdown_Wiki_Link = array(
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
        $obj = Solar::factory('Solar_Markdown_Wiki_Link');
        $this->assertInstance($obj, 'Solar_Markdown_Wiki_Link');
    }
    
    /**
     * 
     * Test -- Cleans up text to replace encoded placeholders with anchors.
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
     * Test -- Gets the list of interwiki mappings.
     * 
     */
    public function testGetInterwiki()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Gets the list of pages found in the source text.
     * 
     */
    public function testGetPages()
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
     * Test -- Parses the source text for wiki page and interwiki links.
     * 
     */
    public function testParse()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Prepares the source text before any parsing occurs.
     * 
     */
    public function testPrepare()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Resets this plugin for a new transformation.
     * 
     */
    public function testReset()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets one anchor attribute.
     * 
     */
    public function testSetAttrib()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets all attributes for one anchor type.
     * 
     */
    public function testSetAttribs()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the callback to check if pages exist.
     * 
     */
    public function testSetCheckPagesCallback()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets one or more interwiki name and href mapping.
     * 
     */
    public function testSetInterwiki()
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
