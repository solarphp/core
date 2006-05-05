<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_FormTextarea extends Test_Solar_View_Helper {
    
    public function testFormTextarea()
    {
        $info = array(
            'name'  => 'test',
            'value' => '"quoted\'s"',
        );
        
        $actual = $this->_view->formTextarea($info);
        $expect = '<textarea name="test">&quot;quoted\'s&quot;</textarea>';
        $this->assertSame($actual, $expect);
    }
}
?>