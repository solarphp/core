<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_FormOptionsTest extends Solar_View_HelperTestCase {
    
    public function testFormOptions()
    {
        $info = array(
            'options' => array(
                'foo' => 'bar',
                'baz' => 'dib',
                'zim' => 'gir',
            ),
        );
        
        // no selection
        $actual = $this->_view->formOptions($info);
        $tmp = array();
        $tmp[] = '<option value="foo" label="bar">bar</option>';
        $tmp[] = '<option value="baz" label="dib">dib</option>';
        $tmp[] = '<option value="zim" label="gir">gir</option>';
        $expect = implode("\n", $tmp);
        $this->assertSame($actual, $expect);
        
        // selected
        $info['value'] = 'baz';
        $actual = $this->_view->formOptions($info);
        $tmp = array();
        $tmp[] = '<option value="foo" label="bar">bar</option>';
        $tmp[] = '<option value="baz" label="dib" selected="selected">dib</option>';
        $tmp[] = '<option value="zim" label="gir">gir</option>';
        $expect = implode("\n", $tmp);
        $this->assertSame($actual, $expect);
    }
}
