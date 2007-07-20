<?php

require_once dirname(__FILE__) . '/../HelperTestCase.php';

class Solar_View_Helper_FormCheckboxTest extends Solar_View_HelperTestCase {
    
    public function testFormCheckbox_default()
    {
        $info = array(
            'name'  => 'test',
        );
        
        // not-checked
        $actual = $this->_view->formCheckbox($info);
        $expect = '<input type="hidden" name="test" value="0" />'
                . '<input type="checkbox" name="test" value="1" />';
        $this->assertSame($actual, $expect);
        
        // checked
        $info['value'] = 1;
        $actual = $this->_view->formCheckbox($info);
        $expect = '<input type="hidden" name="test" value="0" />'
                . '<input type="checkbox" name="test" value="1" checked="checked" />';
        $this->assertSame($actual, $expect);
        
    }
    
    public function testFormCheckbox_withOptions()
    {
        $info = array(
            'name'  => 'test',
            'options' => array('y', 'n'),
        );
        
        // not-checked
        $actual = $this->_view->formCheckbox($info);
        $expect = '<input type="hidden" name="test" value="n" />'
                . '<input type="checkbox" name="test" value="y" />';
        $this->assertSame($actual, $expect);
        
        // checked
        $info['value'] = 'y';
        $actual = $this->_view->formCheckbox($info);
        $expect = '<input type="hidden" name="test" value="n" />'
                . '<input type="checkbox" name="test" value="y" checked="checked" />';
        $this->assertSame($actual, $expect);
    }
    
    public function testFormCheckbox_withLabel()
    {
        $info = array(
            'name'  => 'test',
            'label' => 'Check Me',
        );
        
        // not-checked
        $actual = $this->_view->formCheckbox($info);
        $expect = '<input type="hidden" name="test" value="0" />'
                . '<label><input type="checkbox" name="test" value="1" />Check Me</label>';
        $this->assertSame($actual, $expect);
        
        // checked
        $info['value'] = 1;
        $actual = $this->_view->formCheckbox($info);
        $expect = '<input type="hidden" name="test" value="0" />'
                . '<label><input type="checkbox" name="test" value="1" checked="checked" />Check Me</label>';
        $this->assertSame($actual, $expect);
    }
}
