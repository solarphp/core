<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Plugin_Prefilter extends Test_Solar_Markdown_Plugin {
    
    public function testFilter()
    {
        $text = "Basic text\r\nwith dos lines\nand\t\ttabs\n \n \nand blank lines";
        $expect = "Basic text\nwith dos lines\nand     tabs\n\n\nand blank lines\n\n\n";
        $actual = $this->_rule->filter($text);
        $this->assertSame($actual, $expect);
    }
    
    public function testFilter_unixNewlines()
    {
        $text = "\r\n\r\r\n";
        $expect = "\n\n\n\n\n\n";
        $actual = $this->_rule->filter($text);
        $this->assertSame($actual, $expect);
    }
    
    public function testFilter_addNewlines()
    {
        $text = '';
        $expect = "\n\n\n";
        $actual = $this->_rule->filter($text);
        $this->assertSame($actual, $expect);
    }
    
    public function testFilter_tabsToSpaces()
    {
        $text = "1\t\t22\t\t333\t\t4444\t";
        $expect = "1       22      333     4444    \n\n\n";
        $actual = $this->_rule->filter($text);
        $this->assertSame($actual, $expect);
    }
    
    public function testFilter_blankLines()
    {
        $text = "foo\n  \t  \nbar";
        $expect = "foo\n\nbar\n\n\n";
        $actual = $this->_rule->filter($text);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse()
    {
        $text = 'foo bar baz dib zim gir';
        $actual = $this->_rule->parse($text);
        $expect = $text;
        $this->assertSame($actual, $text);
    }
    
    public function testRender()
    {
        $text = 'foo bar baz dib zim gir';
        $result = $this->_rule->parse($text);
        $actual = $this->_rule->render($result);
        $expect = $text;
        $this->assertSame($actual, $expect);
    }
}
?>