<?php

require_once dirname(__FILE__) . '/../../SolarUnitTest.config.php';

class Solar_Debug_VarTest extends PHPUnit_Framework_TestCase
{
    
    protected $_expect_array;
    protected $_expect_string;
    
    protected $_actual_array = array(
        'foo' => 'bar',
        'baz' => 'dib',
        'zim' => array(
            'gir', 'irk'
        )
    );
    
    protected $_actual_string = 'foo < bar > baz " dib & zim ? gir';
    
    public function setUp()
    {
        // expected output for arrays
        $this->_expect_array = <<<EXPECT
array(3) {
  ["foo"] => string(3) "bar"
  ["baz"] => string(3) "dib"
  ["zim"] => array(2) {
    [0] => string(3) "gir"
    [1] => string(3) "irk"
  }
}

EXPECT;
        
        // expected output for strings
        $this->_expect_string = 'string(33) "foo < bar > baz " dib & zim ? gir"' . "\n";
        
        // Solar_Example class
        $this->_actual_object = Solar::factory('Solar_Example');
        
        // var dumpers
        $this->_var_text = Solar::factory('Solar_Debug_Var', array('output' => 'text'));
        $this->_var_html = Solar::factory('Solar_Debug_Var', array('output' => 'html'));
    }
    
    public function tearDown()
    {
        $this->_expect_array = '';
        $this->_expect_string = '';
        $this->_var_text = '';
        $this->_var_html = '';
    }
    
    
    public function test__construct()
    {
        $var = Solar::factory('Solar_Debug_Var', array('output' => 'text'));
        $this->assertType('Solar_Debug_Var', $var);
    }
    
    public function testFetch_array()
    {
        $this->assertSame(
            $this->_var_text->fetch($this->_actual_array),
            $this->_expect_array
        );
    }
    
    public function testFetch_string()
    {
        $this->assertSame(
            $this->_var_text->fetch($this->_actual_string),
            $this->_expect_string
        );
    }
    
    public function testDisplay_arrayAsHtml()
    {
        $expect = '<pre>'
                . htmlspecialchars($this->_expect_array)
                . '</pre>';
        
        ob_start();
        $this->_var_html->display($this->_actual_array);
        $actual = ob_get_clean();
        
        $this->assertSame(
            $expect,
            $actual
        );
    }
    
    public function testDisplay_stringAsHtml()
    {
        $expect = '<pre>'
                . htmlspecialchars($this->_expect_string)
                . '</pre>';
        
        ob_start();
        $this->_var_html->display($this->_actual_string);
        $actual = ob_get_clean();
        
        $this->assertSame(
            $expect,
            $actual
        );
    }
}
