<?php
require_once Solar::dirname(__FILE__, 1) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Plugin_Uri extends Test_Solar_Markdown_Plugin {
    
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
    
    // should show no changes
    public function testCleanup()
    {
        $source = "foo bar baz";
        $expect = $source;
        $actual = $this->_plugin->cleanup($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse()
    {
        $source = '<http://example.com/?foo=bar&baz=dib>';
        $expect = '<a href="http://example.com/?foo=bar&amp;baz=dib">http://example.com/?foo=bar&amp;baz=dib</a>';
        $actual = $this->_plugin->parse($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_noAngles()
    {
        $source = 'http://example.com/?foo=bar&baz=dib';
        $expect = 'http://example.com/?foo=bar&baz=dib';
        $actual = $this->_plugin->parse($source);
        $this->assertSame($actual, $expect);
    }
}
?>