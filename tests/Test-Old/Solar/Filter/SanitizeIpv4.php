<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Filter_SanitizeIpv4 extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Filter_SanitizeIpv4 = array(
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
        $obj = Solar::factory('Solar_Filter_SanitizeIpv4');
        $this->assertInstance($obj, 'Solar_Filter_SanitizeIpv4');
    }
    
    /**
     * 
     * Test -- Forces the value to an IPv4 address.
     * 
     */
    public function testSanitizeIpv4()
    {
        $this->todo('stub');
    }


}