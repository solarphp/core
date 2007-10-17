<?php

require_once dirname(__FILE__) . '/../../SolarUnitTest.config.php';
class Solar_Class_MapTest extends PHPUnit_Framework_TestCase {
    
    public function test__construct()
    {
        $map = Solar::factory('Solar_Class_Map');
        $this->assertType('Solar_Class_Map', $map);
    }
    
    // if we did a full test, it'd be the whole Solar class map,
    // and that's a bit much to keep up with.
    public function testFetch_limited()
    {
        $dir = Solar_Dir::fix(dirname(__FILE__) . '/../../support/');
        $base = realpath($dir);
        $map = Solar::factory('Solar_Class_Map');
        $map->setBase($base);
        $actual = $map->fetch('Solar_Class_Map');
        $expect = array (
            "Solar_Class_Map_DirOne_TestOne" => Solar_Dir::fix("$base/Solar/Class/Map/DirOne/") . "TestOne.php",
            "Solar_Class_Map_DirOne_TestTwo" => Solar_Dir::fix("$base/Solar/Class/Map/DirOne/") . "TestTwo.php",
            "Solar_Class_Map_TestOne"        => Solar_Dir::fix("$base/Solar/Class/Map/") . "TestOne.php",
            "Solar_Class_Map_TestTwo"        => Solar_Dir::fix("$base/Solar/Class/Map/") . "TestTwo.php",
        );
        
        $this->assertSame($actual, $expect);
    }
}
