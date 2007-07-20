<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Plugin_UriTest extends Solar_Markdown_PluginTestCase {
    
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
        $source = 'foo <http://example.com/?foo=bar&baz=dib> bar';
        $expect = "foo $this->_token bar";
        $actual = $this->_plugin->parse($source);
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testRender()
    {
        $source = '<http://example.com/?foo=bar&baz=dib>';
        $expect = '<a href="http://example.com/?foo=bar&amp;baz=dib">http://example.com/?foo=bar&amp;baz=dib</a>';
        $actual = $this->_spanTransform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_noAngles()
    {
        $source = 'http://example.com/?foo=bar&baz=dib';
        $expect = 'http://example.com/?foo=bar&baz=dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($expect, $actual);
    }
}
