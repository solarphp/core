<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_Date extends Test_Solar_View_Helper {
    
    public function testDate_string()
    {
        $string = 'Nov 7, 1970';
        $actual = $this->_view->date($string);
        $expect = '1970-11-07';
        $this->assertSame($actual, $expect);
    }
    
    public function testDate_int()
    {
        $int = strtotime('Nov 7, 1970');
        $actual = $this->_view->date($int);
        $expect = '1970-11-07';
        $this->assertSame($actual, $expect);
    }
    
    public function testDate_reformat()
    {
        $string = 'Nov 7, 1970';
        $actual = $this->_view->date($string, 'U');
        $expect = strtotime($string);
        $this->assertEquals($actual, $expect);
    }
    
    public function testDate_configFormat()
    {
        $helper = $this->_view->newHelper('date', array('format' => 'U'));
        $string = 'Nov 7, 1970';
        $actual = $helper->date($string);
        $expect = strtotime($string);
        $this->assertEquals($actual, $expect);
    }
}
?>