<?php

class Test_Solar_Debug_Var extends Solar_Test {
    
    protected $_expect_object;
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

    public function __construct($config = null)
    {
        parent::__construct($config);
        
        // expected output for objects
        $this->_expect_object = <<<EXPECT
object(Solar_Test_Example)#32 (1) {
  ["_config:protected"] => array(4) {
    ["foo"] => string(3) "bar"
    ["baz"] => string(3) "dib"
    ["zim"] => string(3) "gaz"
    ["locale"] => string(26) "Solar/Test/Example/Locale/"
  }
}

EXPECT;
        
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
        
        // Solar_Test_Example class
        $this->_actual_object = Solar::factory('Solar_Test_Example');
        
        // var dumpers
        $this->_var_text = Solar::factory('Solar_Debug_Var', array('output' => 'text'));
        $this->_var_html = Solar::factory('Solar_Debug_Var', array('output' => 'html'));
    }
    
    
    public function test__construct()
    {
        $var = Solar::factory('Solar_Debug_Var', array('output' => 'text'));
        $this->_assertInstance($var, 'Solar_Debug_Var');
    }
    
    public function testDump_objectAsText()
    {
        $this->_assertSame(
            $this->_var_text->dump($this->_actual_object),
            $this->_expect_object
        );
    }
    
    public function testDump_arrayAsText()
    {
        $this->_assertSame(
            $this->_var_text->dump($this->_actual_array),
            $this->_expect_array
        );
    }
    
    public function testDump_stringAsText()
    {
        $this->_assertSame(
            $this->_var_text->dump($this->_actual_string),
            $this->_expect_string
        );
    }
    
    public function testDump_objectAsHtml()
    {
        $expect = '<pre>' . htmlspecialchars($this->_expect_object) . '</pre>';
        $this->_assertSame(
            $this->_var_html->dump($this->_actual_object),
            $expect
        );
    }
    
    public function testDump_arrayAsHtml()
    {
        $expect = '<pre>'
                . htmlspecialchars($this->_expect_array)
                . '</pre>';
        
        $this->_assertSame(
            $this->_var_html->dump($this->_actual_array),
            $expect
        );
    }
    
    public function testDump_stringAsHtml()
    {
        $expect = '<pre>'
                . htmlspecialchars($this->_expect_string)
                . '</pre>';
        
        $this->_assertSame(
            $this->_var_html->dump($this->_actual_string),
            $expect
        );
    }
}
?>