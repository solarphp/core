<?php
/**
 * 
 * Concrete class test.
 * 
 */
class Test_Solar_Class_Map extends Solar_Test {
    
    /**
     * 
     * Configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Class_Map = array(
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
        $obj = Solar::factory('Solar_Class_Map');
        $this->assertInstance($obj, 'Solar_Class_Map');
    }
    
    /**
     * 
     * Test -- Gets the class-to-file map for a class hierarchy.
     * 
     */
    public function testFetch()
    {
        $dir  = Solar_Class::dir('Solar', '..');
        $base = realpath($dir);
        
        $map = Solar::factory('Solar_Class_Map');
        $map->setBase($base);
        
        $actual = $map->fetch('Solar_Example');
        $expect = array(
            "Solar_Example"                                         => "$base/Solar/Example.php",
            "Solar_Example_Controller_Page"                         => "$base/Solar/Example/Controller/Page.php",
            "Solar_Example_Exception"                               => "$base/Solar/Example/Exception.php",
            "Solar_Example_Exception_CustomCondition"               => "$base/Solar/Example/Exception/CustomCondition.php",
            "Solar_Example_Model_Areas"                             => "$base/Solar/Example/Model/Areas.php",
            "Solar_Example_Model_Areas_Collection"                  => "$base/Solar/Example/Model/Areas/Collection.php",
            "Solar_Example_Model_Areas_Record"                      => "$base/Solar/Example/Model/Areas/Record.php",
            "Solar_Example_Model_Metas"                             => "$base/Solar/Example/Model/Metas.php",
            "Solar_Example_Model_Metas_Collection"                  => "$base/Solar/Example/Model/Metas/Collection.php",
            "Solar_Example_Model_Metas_Record"                      => "$base/Solar/Example/Model/Metas/Record.php",
            "Solar_Example_Model_Nodes"                             => "$base/Solar/Example/Model/Nodes.php",
            "Solar_Example_Model_Nodes_Collection"                  => "$base/Solar/Example/Model/Nodes/Collection.php",
            "Solar_Example_Model_Nodes_Record"                      => "$base/Solar/Example/Model/Nodes/Record.php",
            "Solar_Example_Model_Taggings"                          => "$base/Solar/Example/Model/Taggings.php",
            "Solar_Example_Model_Taggings_Collection"               => "$base/Solar/Example/Model/Taggings/Collection.php",
            "Solar_Example_Model_Taggings_Record"                   => "$base/Solar/Example/Model/Taggings/Record.php",
            "Solar_Example_Model_Tags"                              => "$base/Solar/Example/Model/Tags.php",
            "Solar_Example_Model_Tags_Collection"                   => "$base/Solar/Example/Model/Tags/Collection.php",
            "Solar_Example_Model_Tags_Record"                       => "$base/Solar/Example/Model/Tags/Record.php",
            "Solar_Example_Model_TestSolarDib"                      => "$base/Solar/Example/Model/TestSolarDib.php",
            "Solar_Example_Model_TestSolarFoo"                      => "$base/Solar/Example/Model/TestSolarFoo.php",
            "Solar_Example_Model_TestSolarFoo_Bar"                  => "$base/Solar/Example/Model/TestSolarFoo/Bar.php",
            "Solar_Example_Model_TestSolarFoo_Bar_Record"           => "$base/Solar/Example/Model/TestSolarFoo/Bar/Record.php",
            "Solar_Example_Model_TestSolarFoo_Collection"           => "$base/Solar/Example/Model/TestSolarFoo/Collection.php",
            "Solar_Example_Model_TestSolarFoo_Record"               => "$base/Solar/Example/Model/TestSolarFoo/Record.php",
            "Solar_Example_Model_TestSolarSpecialCols"              => "$base/Solar/Example/Model/TestSolarSpecialCols.php",
            "Solar_Example_Model_TestSolarSpecialCols_Collection"   => "$base/Solar/Example/Model/TestSolarSpecialCols/Collection.php",
            "Solar_Example_Model_TestSolarSpecialCols_Record"       => "$base/Solar/Example/Model/TestSolarSpecialCols/Record.php",
            "Solar_Example_Model_TestSolarSqlDescribe"              => "$base/Solar/Example/Model/TestSolarSqlDescribe.php",
            "Solar_Example_Model_TestSolarSqlDescribe_Collection"   => "$base/Solar/Example/Model/TestSolarSqlDescribe/Collection.php",
            "Solar_Example_Model_TestSolarSqlDescribe_Record"       => "$base/Solar/Example/Model/TestSolarSqlDescribe/Record.php",
            "Solar_Example_Model_Users"                             => "$base/Solar/Example/Model/Users.php",
            "Solar_Example_Model_Users_Collection"                  => "$base/Solar/Example/Model/Users/Collection.php",
            "Solar_Example_Model_Users_Record"                      => "$base/Solar/Example/Model/Users/Record.php",
        );
        
        $this->assertSame($actual, $expect);
    }
    
    /**
     * 
     * Test -- Gets the base directory for the class map.
     * 
     */
    public function testGetBase()
    {
        $dir  = Solar_Class::dir('Solar', '..');
        $base = Solar_Dir::fix(realpath($dir));
        
        $map = Solar::factory('Solar_Class_Map');
        $map->setBase($base);
        
        $actual = $map->getBase();
        $this->assertSame($actual, $base);
    }
    
    /**
     * 
     * Test -- Sets the base directory for the class map.
     * 
     */
    public function testSetBase()
    {
        $dir  = Solar_Class::dir('Solar', '..');
        $base = Solar_Dir::fix(realpath($dir));
        
        $map = Solar::factory('Solar_Class_Map');
        $map->setBase($base);
        
        $actual = $map->getBase();
        $this->assertSame($actual, $base);
    }
}
