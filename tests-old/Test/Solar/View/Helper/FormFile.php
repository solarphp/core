<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_FormFile extends Test_Solar_View_Helper {
    
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
?>