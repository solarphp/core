<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_App_Bookmarks extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_App_Bookmarks = array(
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
        $obj = Solar::factory('Solar_App_Bookmarks');
        $this->assertInstance($obj, 'Solar_App_Bookmarks');
    }
    
    /**
     * 
     * Test -- Try to force users to define what their view variables are.
     * 
     */
    public function test__get()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Try to force users to define what their view variables are.
     * 
     */
    public function test__set()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Adds a new bookmark for a signed-in user.
     * 
     */
    public function testActionAdd()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Allows a signed-in user to edit an existing bookmark.
     * 
     */
    public function testActionEdit()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Shows a generic error page.
     * 
     */
    public function testActionError()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Handles JavaScript bookmarking requests from offsite.
     * 
     */
    public function testActionQuick()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Shows a list of bookmarks filtered by tag, regardless of owner.
     * 
     */
    public function testActionTag()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Shows all bookmarks for an owner, optionally filtered by tag.
     * 
     */
    public function testActionUser()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Executes the requested action and displays its output.
     * 
     */
    public function testDisplay()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Executes the requested action and returns its output with layout.
     * 
     */
    public function testFetch()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Sets the name for this page-controller; generally used only by the  front-controller when static routing leads to this page.
     * 
     */
    public function testSetController()
    {
        $this->todo('stub');
    }
    
    /**
     * 
     * Test -- Injects the front-controller object that invoked this page-controller.
     * 
     */
    public function testSetFrontController()
    {
        $this->todo('stub');
    }
}
