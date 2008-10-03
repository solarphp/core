<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Json_Checker extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Json_Checker = array(
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
        $obj = Solar::factory('Solar_Json_Checker');
        $this->assertInstance($obj, 'Solar_Json_Checker');
    }
    
    /**
     * 
     * Test -- The isValid method takes a UTF-16 encoded string and determines if it is a syntactically correct JSON text.
     * 
     */
    public function testIsValid()
    {
        $checker = Solar::factory('Solar_Json_Checker');
        
        $dir = Solar_Class::dir('Test_Solar_Json', '_support');
        $tests = scandir($dir);
        natsort($tests);
        
        foreach ($tests as $file) {
            if (substr($file, 0, 4) == 'pass' && substr($file, -4) == 'json') {
                $this->diag($file);
                $before = file_get_contents($dir . $file);
                $this->assertTrue($checker->isValid($before));
            }
        }
    }
    
    public function testIsValid_failures()
    {
        $checker = Solar::factory('Solar_Json_Checker');
        
        $dir = Solar_Class::dir('Test_Solar_Json', '_support');
        $tests = scandir($dir);
        natsort($tests);
        
        foreach ($tests as $file) {
            if (substr($file, 0, 4) == 'fail' && substr($file, -4) == 'json') {
                $this->diag($file);
                $before = file_get_contents($dir . $file);
                $this->assertFalse($checker->isValid($before));
            }
        }
    }
}
