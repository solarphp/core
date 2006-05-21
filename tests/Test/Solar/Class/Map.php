<?php

class Test_Solar_Class_Map extends Solar_Test {
    
    public function __construct($config = null)
    {
        parent::__construct($config);
    }
    
    public function _destruct()
    {
        parent::__destruct();
    }
    
    public function setup()
    {
        parent::setup();
    }
    
    public function teardown()
    {
        parent::teardown();
    }
    
    public function test__construct()
    {
        $map = Solar::factory('Solar_Class_Map');
        $this->assertInstance($map, 'Solar_Class_Map');
    }
    
    // if we did a full test, it'd be the whole Solar class map,
    // and that's a bit much to keep up with.
    public function testFetch_limited()
    {
        $base = Solar::config('Test', 'include_path');
        $map = Solar::factory('Solar_Class_Map');
        $actual = $map->fetch($base, 'Solar_Test');
        $expect = array (
            "Solar_Test" => "$base/Solar/Test.php",
            "Solar_Test_Example" => "$base/Solar/Test/Example.php",
            "Solar_Test_Example_Exception" => "$base/Solar/Test/Example/Exception.php",
            "Solar_Test_Example_Exception_CustomCondition" => "$base/Solar/Test/Example/Exception/CustomCondition.php",
            "Solar_Test_Exception" => "$base/Solar/Test/Exception.php",
            "Solar_Test_Exception_Fail" => "$base/Solar/Test/Exception/Fail.php",
            "Solar_Test_Exception_Skip" => "$base/Solar/Test/Exception/Skip.php",
            "Solar_Test_Exception_Todo" => "$base/Solar/Test/Exception/Todo.php",
            "Solar_Test_Suite" => "$base/Solar/Test/Suite.php",
        );

        $this->assertSame($actual, $expect);
    }
}
?>