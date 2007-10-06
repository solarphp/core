<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_StyleTest extends Solar_View_HelperTestCase {
    
    public function testStyle()
    {
        $actual = $this->_view->style('styles.css');
        $expect = '<style type="text/css" media="screen">'
                . '@import url("/public/styles.css");</style>';
        $this->assertSame($actual, $expect);
    }
    
    public function testStyle_Remote()
    {
        $actual = $this->_view->style('http://something.com/path/to/styles.css');
        
        $expect = '<style type="text/css" media="screen">'
                . '@import url("http://something.com/path/to/styles.css");</style>';
                
        $this->assertSame($actual, $expect);
    }

}
