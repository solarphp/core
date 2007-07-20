<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_FormRadioTest extends Solar_View_HelperTestCase {
    
    public function testFormRadio()
    {
        $info = array(
            'name'    => 'test',
            'options' => array(
                'foo' => 'bar',
                'baz' => 'dib',
                'zim' => 'gir',
            ),
        );
        
        // no selection
        $actual = $this->_view->formRadio($info);
        $tmp = array();
        $tmp[] = '<input type="hidden" name="test" value="" />';
        $tmp[] = '<label><input type="radio" name="test" value="foo" />bar</label>';
        $tmp[] = '<label><input type="radio" name="test" value="baz" />dib</label>';
        $tmp[] = '<label><input type="radio" name="test" value="zim" />gir</label>';
        $expect = implode("\n", $tmp);
        $this->assertSame($actual, $expect);
        
        // selected
        $info['value'] = 'baz';
        $actual = $this->_view->formRadio($info);
        $tmp = array();
        $tmp[] = '<input type="hidden" name="test" value="" />';
        $tmp[] = '<label><input type="radio" name="test" value="foo" />bar</label>';
        $tmp[] = '<label><input type="radio" name="test" value="baz" checked="checked" />dib</label>';
        $tmp[] = '<label><input type="radio" name="test" value="zim" />gir</label>';
        $expect = implode("\n", $tmp);
        $this->assertSame($actual, $expect);
    }
}
