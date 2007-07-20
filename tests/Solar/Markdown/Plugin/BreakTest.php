<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Plugin_BreakTest extends Solar_Markdown_PluginTestCase {
    
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
        $this->assertSame($expect, $actual);
    }
    
    // should show no changes
    public function testCleanup()
    {
        $source = "foo bar baz";
        $expect = $source;
        $actual = $this->_plugin->cleanup($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testParse()
    {
        $source = "line\nline \nline  \nline   \n";
        $expect = "line\nline \nline{$this->_token}\nline{$this->_token}\n";
        $actual = $this->_plugin->parse($source);
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testRender()
    {
        $source = "line\nline \nline  \nline   \n";
        $expect = "line\nline \nline<br />\nline<br />\n";
        $actual = $this->_spanTransform($source);
        $this->assertSame($expect, $actual);
    }
}
