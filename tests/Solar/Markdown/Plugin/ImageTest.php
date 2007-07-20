<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Plugin_ImageTest extends Solar_Markdown_PluginTestCase {
    
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
        $source = 'foo bar ![alt text](/path/to/image) baz dib';
        $expect = "foo bar $this->_token baz dib";
        $actual = $this->_plugin->parse($source);
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testRender()
    {
        $source = 'foo bar ![alt text](/path/to/image) baz dib';
        $expect = 'foo bar <img src="/path/to/image" alt="alt text" /> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_inlineWithTitle()
    {
        $source = 'foo bar ![alt text](/path/to/image "with title") baz dib';
        $expect = 'foo bar <img src="/path/to/image" alt="alt text" title="with title" /> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_reference()
    {
        $this->_markdown->setLink('alt text', '/path/to/image', 'with title');
        
        $source = 'foo bar ![alt text][] baz dib';
        $expect = 'foo bar <img src="/path/to/image" alt="alt text" title="with title" /> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_referenceDifferentAlt()
    {
        $this->_markdown->setLink('alt text', '/path/to/image', 'with title');
        
        $source = 'foo bar ![inline text][alt text] baz dib';
        $expect = 'foo bar <img src="/path/to/image" alt="inline text" title="with title" /> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($expect, $actual);
    }
}
