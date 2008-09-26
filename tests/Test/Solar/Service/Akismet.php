<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Service_Akismet extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Service_Akismet = array(
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
        $obj = Solar::factory('Solar_Service_Akismet');
        $this->assertInstance($obj, 'Solar_Service_Akismet');
    }
    
    /**
     * 
     * Test -- Checks the comment data with Akismet to see if it is spam.
     * 
     */
    public function testCommentCheck()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Get the most-recent response sent from Akismet.
     * 
     */
    public function testGetResponse()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Submits data to Akismet to establish it as ham (i.e., not spam).
     * 
     */
    public function testSubmitHam()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Submits data to Akismet to establish it as spam.
     * 
     */
    public function testSubmitSpam()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Verifies the API key with Akismet.
     * 
     */
    public function testVerifyKey()
    {
        $this->todo('stub');
    }
}
