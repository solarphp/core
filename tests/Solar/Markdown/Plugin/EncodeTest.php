<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Plugin_EncodeTest extends Solar_Markdown_PluginTestCase {
    
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
        $source = "\\.\\{\\}";
        $actual = $this->_plugin->parse($source);
        $expect = $this->_encode . $this->_encode . $this->_encode;
        $this->assertRegExp("@$expect@", $actual);
    }
}
