<?php
require_once Solar::dirname(__FILE__, 1) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Wiki_PageLink extends Test_Solar_Markdown_Plugin {
    
    protected $_encode = "\x1BSolar_Markdown_Wiki_PageLink:.*?\x1B";
    
    public function testIsBlock()
    {
        $this->assertFalse($this->_plugin->isBlock());
    }
    
    public function testIsSpan()
    {
        $this->assertTrue($this->_plugin->isSpan());
    }
    
    // should show no changes
    public function testPrepare()
    {
        $source = "foo bar baz";
        $expect = $source;
        $actual = $this->_plugin->prepare($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse()
    {
        $source = 'foo [[page name]] bar';
        $actual = $this->_plugin->parse($source);
        $expect = "foo {$this->_encode} bar";
        $this->assertRegex($actual, "@$expect@");
    }
}
?>