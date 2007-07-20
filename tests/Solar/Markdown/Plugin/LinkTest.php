<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Plugin_LinkTest extends Solar_Markdown_PluginTestCase {
    
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
        $source = 'foo bar [display text](/path/to/file) baz dib';
        $expect = "foo bar $this->_token baz dib";
        $actual = $this->_plugin->parse($source);
        $this->assertRegExp("@$expect@", $actual);
    }
    
    public function testRender()
    {
        $source = 'foo bar [display text](/path/to/file) baz dib';
        $expect = 'foo bar <a href="/path/to/file">display text</a> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_inlineWithTitle()
    {
        $source = 'foo bar [display text](/path/to/file "with title") baz dib';
        $expect = 'foo bar <a href="/path/to/file" title="with title">display text</a> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_inlineWithAngles()
    {
        $source = 'foo bar [display text](</path/to/file>) baz dib';
        $expect = 'foo bar <a href="/path/to/file">display text</a> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_reference()
    {
        $this->_markdown->setLink('display text', '/path/to/file', 'with title');
        
        $source = 'foo bar [display text][] baz dib';
        $expect = 'foo bar <a href="/path/to/file" title="with title">display text</a> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testRender_referenceDifferentAlt()
    {
        $this->_markdown->setLink('display text', '/path/to/file', 'with title');
        
        $source = 'foo bar [inline-text][display text] baz dib';
        $expect = 'foo bar <a href="/path/to/file" title="with title">inline-text</a> baz dib';
        $actual = $this->_spanTransform($source);
        $this->assertSame($expect, $actual);
    }
}
