<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_FormFileTest extends Solar_View_HelperTestCase {
    
    public function testFormFile()
    {
        $info = array(
            'name'  => 'test',
        );
        
        $actual = $this->_view->formFile($info);
        $expect = '<input type="file" name="test" value="" />';
        $this->assertSame($actual, $expect);
    }
}
