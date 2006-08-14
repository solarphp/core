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
        $source = 'foo bar ![alt text](/path/to/image.jpg.jpg) baz dib';
        $expect = 'foo bar <img src="/path/to/image.jpg.jpg" alt="alt text" /> baz dib';
        $actual = $this->_plugin->parse($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_inlineWithTitle()
    {
        $source = 'foo bar ![alt text](/path/to/image.jpg.jpg "with title") baz dib';
        $expect = 'foo bar <img src="/path/to/image.jpg.jpg" alt="alt text" title="with title" /> baz dib';
        $actual = $this->_plugin->parse($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_reference()
    {
        $this->_markdown->setLink('alt text', '/path/to/image.jpg.jpg', 'with title');
        
        $source = 'foo bar ![alt text][] baz dib';
        $expect = 'foo bar <img src="/path/to/image.jpg.jpg" alt="alt text" title="with title" /> baz dib';
        $actual = $this->_plugin->parse($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_referenceDifferentAlt()
    {
        $this->_markdown->setLink('alt text', '/path/to/image.jpg.jpg', 'with title');
        
        $source = 'foo bar ![inline text][alt text] baz dib';
        $expect = 'foo bar <img src="/path/to/image.jpg.jpg" alt="inline text" title="with title" /> baz dib';
        $actual = $this->_plugin->parse($source);
        $this->assertSame($actual, $expect);
    }
}
?>