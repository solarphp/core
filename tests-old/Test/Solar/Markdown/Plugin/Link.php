<?php
require_once Solar::dirname(__FILE__, 1) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Plugin_Link extends Test_Solar_Markdown_Plugin {
    
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
        $source = 'foo bar [display text](/path/to/file) baz dib';
        $expect = "foo bar $this->_token baz dib";
        $actual = $this->_plugin->parse($source);
        $this->assertRegex($actual, "@$expect@");
    }
    
    public function testRender()
    {
        $source = 'foo bar [display text](/path/to/file) baz dib';
        $expect = 'foo bar <a href="/path/to/file">display text</a> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testRender_inlineWithTitle()
    {
        $source = 'foo bar [display text](/path/to/file "with title") baz dib';
        $expect = 'foo bar <a href="/path/to/file" title="with title">display text</a> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testRender_inlineWithAngles()
    {
        $source = 'foo bar [display text](</path/to/file>) baz dib';
        $expect = 'foo bar <a href="/path/to/file">display text</a> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testRender_reference()
    {
        $this->_markdown->setLink('display text', '/path/to/file', 'with title');
        
        $source = 'foo bar [display text][] baz dib';
        $expect = 'foo bar <a href="/path/to/file" title="with title">display text</a> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testRender_referenceDifferentAlt()
    {
        $this->_markdown->setLink('display text', '/path/to/file', 'with title');
        
        $source = 'foo bar [inline-text][display text] baz dib';
        $expect = 'foo bar <a href="/path/to/file" title="with title">inline-text</a> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($actual, $expect);
    }
}
?>