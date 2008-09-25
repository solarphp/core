<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_View_Helper_GetTextRaw extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_View_Helper_GetTextRaw = array(
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
        $obj = Solar::factory('Solar_View_Helper_GetTextRaw');
        $this->assertInstance($obj, 'Solar_View_Helper_GetTextRaw');
    }
    
    /**
     * 
     * Test -- Returns a localized string WITH NO ESCAPING.
     * 
     */
    public function testGetTextRaw()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the class used for translations.
     * 
     */
    public function testSetClass()
    {
        $this->todo('stub');
    }
}
