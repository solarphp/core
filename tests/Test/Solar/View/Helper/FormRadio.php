<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/Helper.php';

class Test_Solar_View_Helper_FormRadio extends Test_Solar_View_Helper {
    
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
?>