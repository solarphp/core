<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_FormTextareaTest extends Solar_View_HelperTestCase {
    
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
