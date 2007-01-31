<?php
require_once dirname(__FIlE__) . '/../../SolarUnitTest.config.php';

class Solar_Debug_TimerTest extends PHPUnit_Framework_TestCase
{
    
    public function test__construct()
    {
        $timer = Solar::factory('Solar_Debug_Timer');
        $this->assertType('Solar_Debug_Timer', $timer);
    }
    
    public function testAll()
    {
        // does the class create the locale config?
        $timer = Solar::factory('Solar_Debug_Timer', array('output' => 'text'));
        
        
        $mark['__start'] = microtime(true);
        $timer->start();
        for ($i = 0; $i < 4; $i++) {
            $wait = rand(1,2);
            sleep($wait);
            $mark[$i] = microtime(true);
            $timer->mark($i);
        }
        $mark['__stop'] = microtime(true);
        $timer->stop();
        
        // get the timer profile
        $profile = $timer->profile();
        
        // make sure we hit all the marks
        $this->assertTrue(count($profile) == count($mark));
        
        foreach ($profile as $val) {
            // make sure the profiled times are near the
            // times we marked
            $key = $val['name'];
            $diff = abs($val['time'] - $mark[$key]);
            $this->assertTrue($diff <= 0.0005);
        }
    }
}
?>