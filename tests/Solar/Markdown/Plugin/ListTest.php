<?php
require_once dirname(__FILE__) . '/../PluginTestCase.php';

class Solar_Markdown_Plugin_ListTest extends Solar_Markdown_PluginTestCase {
    
    public function testIsBlock()
    {
        $this->assertTrue($this->_plugin->isBlock());
    }
    
    public function testIsSpan()
    {
        $this->assertFalse($this->_plugin->isSpan());
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
        $source = array();
        $source[] = 'foo';
        $source[] = "";
        $source[] = "* foo";
        $source[] = "* bar";
        $source[] = "* baz";
        $source[] = "";
        $source[] = "bar";
        $source[] = "";
        $source[] = "1. dib";
        $source[] = "2. zim";
        $source[] = "3. gir";
        $source[] = "";
        $source[] = "baz";
        $source = implode("\n", $source). "\n\n";
        
        $expect = array();
        $expect[] = 'foo';
        $expect[] = "";
        $expect[] = $this->_token;
        $expect[] = "";
        $expect[] = 'bar';
        $expect[] = "";
        $expect[] = $this->_token;
        $expect[] = "";
        $expect[] = 'baz';
        $expect = implode("\n", $expect);
        
        $actual = $this->_plugin->parse($source);
        $this->assertRegExp("@$expect@", $actual);
    }
    
    // parse a pair of simple lists
    public function testRender()
    {
        $source = array();
        $source[] = "* foo";
        $source[] = "* bar";
        $source[] = "* baz";
        $source[] = "";
        $source[] = "sep";
        $source[] = "";
        $source[] = "1. dib";
        $source[] = "2. zim";
        $source[] = "3. gir";
        $source = implode("\n", $source). "\n\n";
        
        $expect = array();
        $expect[] = $this->_tag('ul');
        $expect[] = $this->_tag('li') . "foo" . $this->_tag('/li');
        $expect[] = $this->_tag('li') . "bar" . $this->_tag('/li');
        $expect[] = $this->_tag('li') . "baz" . $this->_tag('/li');
        $expect[] = $this->_tag('/ul');
        $expect[] = "";
        $expect[] = "sep";
        $expect[] = "";
        $expect[] = $this->_tag('ol');
        $expect[] = $this->_tag('li') . "dib" . $this->_tag('/li');
        $expect[] = $this->_tag('li') . "zim" . $this->_tag('/li');
        $expect[] = $this->_tag('li') . "gir" . $this->_tag('/li');
        $expect[] = $this->_tag('/ol');
        $expect = implode("\n*", $expect);
        
        $actual = $this->_markdown->transform($source);
        $this->assertRegExp("@$expect@", $actual);
    }
    
    // parse a nested list series
    public function testRender_nested()
    {
        $source[] = "* foo";
        $source[] = "\t* bar";
        $source[] = "\t* baz";
        $source[] = "* dib";
        $source[] = "\t* zim";
        $source[] = "\t* gir";
        $source = implode("\n", $source). "\n\n";
        
        $expect = array();
        $expect[] = $this->_tag('ul');
        $expect[] = $this->_tag('li') . "foo";
        $expect[] = $this->_tag('ul');
        $expect[] = $this->_tag('li') . "bar" . $this->_tag('/li');
        $expect[] = $this->_tag('li') . "baz" . $this->_tag('/li');
        $expect[] = $this->_tag('/ul') . $this->_tag('/li');
        $expect[] = $this->_tag('li') . "dib";
        $expect[] = $this->_tag('ul');
        $expect[] = $this->_tag('li') . "zim" . $this->_tag('/li');
        $expect[] = $this->_tag('li') . "gir" . $this->_tag('/li');
        $expect[] = $this->_tag('/ul') . $this->_tag('/li');
        $expect[] = $this->_tag('/ul');
        $expect = implode('\s*', $expect);
        
        $actual = $this->_markdown->transform($source);
        $this->assertRegExp("@$expect@", $actual);
    }
}
