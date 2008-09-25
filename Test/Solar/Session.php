<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Session extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Session = array(
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
        $obj = Solar::factory('Solar_Session');
        $this->assertInstance($obj, 'Solar_Session');
    }
    
    /**
     * 
     * Test -- Appends a normal value to a key.
     * 
     */
    public function testAdd()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Appends a flash value to a key.
     * 
     */
    public function testAddFlash()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Gets a normal value by key, or an alternative default value if the key does not exist.
     * 
     */
    public function testGet()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Gets a flash value by key, thereby removing the value.
     * 
     */
    public function testGetFlash()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Whether or not the session currently has a particular flash key stored.
     * 
     */
    public function testHasFlash()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Regenerates the session ID and deletes the previous session store.
     * 
     */
    public function testRegenerateId()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Resets (clears) all normal keys and values.
     * 
     */
    public function testReset()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Resets both "normal" and "flash" values.
     * 
     */
    public function testResetAll()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Resets (clears) all flash keys and values.
     * 
     */
    public function testResetFlash()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets a normal value by key.
     * 
     */
    public function testSet()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the class segment for $_SESSION.
     * 
     */
    public function testSetClass()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets a flash value by key.
     * 
     */
    public function testSetFlash()
    {
        $this->todo('stub');
    }
}
