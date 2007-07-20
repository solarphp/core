<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Plugin_PrefilterTest extends Solar_Markdown_PluginTestCase {
    
    public function testIsBlock()
    {
        $this->assertFalse($this->_plugin->isBlock());
    }
    
    public function testIsSpan()
    {
        $this->assertFalse($this->_plugin->isSpan());
    }
    
    public function testPrepare()
    {
        $text = "Basic text\r\nwith dos lines\nand\t\ttabs\n \n \nand blank lines";
        $expect = "Basic text\nwith dos lines\nand     tabs\n\n\nand blank lines\n\n\n";
        $actual = $this->_plugin->prepare($text);
        $this->assertSame($expect, $actual);
    }
    
    public function testPrepare_unixNewlines()
    {
        $text = "\r\n\r\r\n";
        $expect = "\n\n\n\n\n\n";
        $actual = $this->_plugin->prepare($text);
        $this->assertSame($expect, $actual);
    }
    
    public function testPrepare_addNewlines()
    {
        $text = '';
        $expect = "\n\n\n";
        $actual = $this->_plugin->prepare($text);
        $this->assertSame($expect, $actual);
    }
    
    public function testPrepare_tabsToSpaces()
    {
        $text = "1\t\t22\t\t333\t\t4444\t";
        $expect = "1       22      333     4444    \n\n\n";
        $actual = $this->_plugin->prepare($text);
        $this->assertSame($expect, $actual);
    }
    
    public function testPrepare_blankLines()
    {
        $text = "foo\n  \t  \nbar";
        $expect = "foo\n\nbar\n\n\n";
        $actual = $this->_plugin->prepare($text);
        $this->assertSame($expect, $actual);
    }
    
    public function testParse()
    {
        $text = 'foo bar baz dib zim gir';
        $actual = $this->_plugin->parse($text);
        $expect = $text;
        $this->assertSame($actual, $text);
    }
    
    public function testCleanup()
    {
        $text = 'foo bar baz dib zim gir';
        $actual = $this->_plugin->cleanup($text);
        $expect = $text;
        $this->assertSame($actual, $text);
    }
}
