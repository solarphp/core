<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Plugin_StripLinkDefsTest extends Solar_Markdown_PluginTestCase {
    
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
        $source   = array();
        $source[] = "foo bar";
        $source[] = "[a]:         href1";
        $source[] = "[b]:         href2 \"Title2\"";
        $source[] = "[c]:         href3\n    \"Title3\"";
        $source[] = "[UpperCase]: href4";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect = "foo bar\nbaz dib";
        $actual = $this->_plugin->prepare($source);
        $this->assertSame($expect, $actual);
    }
    
    public function testPrepare_getLinks()
    {
        $source   = array();
        $source[] = "foo bar";
        $source[] = "[a]: href1";
        $source[] = "[b]: href2 \"Title2\"";
        $source[] = "[c]: href3\n    \"Title3\"";
        $source[] = "[UpperCase]: href4";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $this->_plugin->prepare($source);
        
        $expect = array(
            'a' => array(
                'href' => 'href1',
                'title' => null,
            ),
            'b' => array(
                'href' => 'href2',
                'title' => 'Title2',
            ),
            'c' => array(
                'href' => 'href3',
                'title' => 'Title3',
            ),
            'uppercase' => array(
                'href' => 'href4',
                'title' => null,
            ),
        );
        
        $actual = $this->_markdown->getLinks();
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
