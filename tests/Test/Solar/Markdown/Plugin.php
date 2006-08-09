<?php
abstract class Test_Solar_Markdown_Plugin extends Solar_Test {
    
    protected $_class;
    
    protected $_rule;
    
    protected $_text;
    
    var $_token = "\x0E.{32}\x0F";
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_class = substr(get_class($this), 5);
    }
    
    public function setup()
    {
        $this->_rule = Solar::factory($this->_class);
    }
    
    public function test__construct()
    {
        $this->assertInstance($this->_rule, $this->_class);
    }
    
    public function testFilter()
    {
        $this->todo('needs a filter test');
    }
    
    public function testParse()
    {
        $this->todo('needs a parse test');
    }
    
    public function testRender()
    {
        $this->todo('needs a render test');
    }
}
?>