<?php
/**
 * Parent test.
 */
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Adapter.php';

/**
 * 
 * Adapter class test.
 * 
 */
class Test_Solar_Mail_Transport_Adapter_Smtp extends Test_Solar_Mail_Transport_Adapter {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Mail_Transport_Adapter_Smtp = array(
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
        $this->todo('need adapter-specific config');
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
        $obj = Solar::factory('Solar_Mail_Transport_Adapter_Smtp');
        $this->assertInstance($obj, 'Solar_Mail_Transport_Adapter_Smtp');
    }
    
    /**
     * 
     * Test -- Sends the Solar_Mail_Message through an SMTP server.
     * 
     */
    public function test_send()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sends a Solar_Mail_Message.
     * 
     */
    public function testSend()
    {
        $this->todo('stub');
    }


}
