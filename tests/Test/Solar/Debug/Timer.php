<?php
class Test_Solar_Debug_Timer extends Solar_Test {
    
    public function test__construct()
    {
        $timer = Solar::factory('Solar_Debug_Timer');
        $this->_assertInstance($timer, 'Solar_Debug_Timer');
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
        $this->_assertTrue(count($profile) == count($mark));
        
        foreach ($profile as $val) {
            // make sure the profiled times are near the
            // times we marked
            $key = $val['name'];
            $diff = abs($val['time'] - $mark[$key]);
            $this->_assertTrue($diff <= 0.0005);
        }
    }
}
?>