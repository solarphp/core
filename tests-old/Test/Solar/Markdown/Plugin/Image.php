<?php
require_once Solar::dirname(__FILE__, 1) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Plugin_Image extends Test_Solar_Markdown_Plugin {
    
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
        $source = 'foo bar ![alt text](/path/to/image) baz dib';
        $expect = "foo bar $this->_token baz dib";
        $actual = $this->_plugin->parse($source);
        $this->assertRegex($actual, "@$expect@");
    }
    
    public function testRender()
    {
        $source = 'foo bar ![alt text](/path/to/image) baz dib';
        $expect = 'foo bar <img src="/path/to/image" alt="alt text" /> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testRender_inlineWithTitle()
    {
        $source = 'foo bar ![alt text](/path/to/image "with title") baz dib';
        $expect = 'foo bar <img src="/path/to/image" alt="alt text" title="with title" /> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testRender_reference()
    {
        $this->_markdown->setLink('alt text', '/path/to/image', 'with title');
        
        $source = 'foo bar ![alt text][] baz dib';
        $expect = 'foo bar <img src="/path/to/image" alt="alt text" title="with title" /> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testRender_referenceDifferentAlt()
    {
        $this->_markdown->setLink('alt text', '/path/to/image', 'with title');
        
        $source = 'foo bar ![inline text][alt text] baz dib';
        $expect = 'foo bar <img src="/path/to/image" alt="inline text" title="with title" /> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($actual, $expect);
    }
}
?>