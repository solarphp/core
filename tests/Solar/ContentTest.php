<?php

require_once dirname(__FILE__) . '/../SolarUnitTest.config.php';
require_once 'Solar/Content.php';

class Solar_ContentTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        Solar::start('config.inc.php');
    }
    
    public function tearDown() {
        Solar::stop();
    }
    
    public function testCanInstantiateThroughFactory() {
        $sql = Solar::factory('Solar_Sql');
        $content = Solar::factory('Solar_Content', array('sql' => $sql));
        $this->assertTrue($content instanceof Solar_Content);
    }
}
