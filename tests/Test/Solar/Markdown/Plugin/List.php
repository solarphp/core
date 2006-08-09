<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Plugin_List extends Test_Solar_Markdown_Plugin {
    
    // should show no changes
    public function testFilter()
    {
        $source = "foo bar baz";
        $expect = $source;
        $actual = $this->_rule->filter($source);
        $this->assertSame($actual, $expect);
    }
    
    // parse a pair of simple lists
    public function testParse()
    {
        $source = array();
        $source[] = "* foo";
        $source[] = "* bar";
        $source[] = "* baz";
        $source[] = "";
        $source[] = "1. dib";
        $source[] = "2. zim";
        $source[] = "3. gir";
        $source = implode("\n", $source). "\n\n";
        
        $expect = array();
        $expect[] = $this->_token; // <ul>
        $expect[] = $this->_token . "foo" . $this->_token;
        $expect[] = $this->_token . "bar" . $this->_token;
        $expect[] = $this->_token . "baz" . $this->_token;
        $expect[] = $this->_token; // </ul>
        $expect[] = $this->_token; // <ol>
        $expect[] = $this->_token . "dib" . $this->_token;
        $expect[] = $this->_token . "zim" . $this->_token;
        $expect[] = $this->_token . "gir" . $this->_token;
        $expect[] = $this->_token; // </ol>
        $expect = implode("\n*", $expect);
        
        $actual = $this->_rule->parse($source);
        $this->assertRegex($actual, "/$expect/");
    }
    
    // parse a nested list series
    public function testParse_nested()
    {
        $source[] = "* foo";
        $source[] = "\t* bar";
        $source[] = "\t* baz";
        $source[] = "* dib";
        $source[] = "\t* zim";
        $source[] = "\t* gir";
        $source = implode("\n", $source). "\n\n";
        
        $expect = array();
        $expect[] = $this->_token; // <ul>
        $expect[] = $this->_token . "foo";
        $expect[] = $this->_token; // another <ul>
        $expect[] = $this->_token . "bar" . $this->_token;
        $expect[] = $this->_token . "baz" . $this->_token;
        $expect[] = $this->_token . $this->_token; // </ul></li>
        $expect[] = $this->_token . "dib";
        $expect[] = $this->_token; // another <ul>
        $expect[] = $this->_token . "zim" . $this->_token;
        $expect[] = $this->_token . "gir" . $this->_token;
        $expect[] = $this->_token . $this->_token; // </ul></li>
        $expect[] = $this->_token; // </ul>
        $expect = implode('\s*', $expect);
        
        $actual = $this->_rule->parse($source);
        $this->assertRegex($actual, "/$expect/");
    }
    
    // parse a nested list series
    public function testParse_mixedNested()
    {
        $source[] = "1. foo";
        $source[] = "\t* bar";
        $source[] = "\t* baz";
        $source[] = "2. dib";
        $source[] = "\t* zim";
        $source[] = "\t* gir";
        $source = implode("\n", $source). "\n\n";
        
        $expect = array();
        $expect[] = $this->_token; // <ol>
        $expect[] = $this->_token . "foo";
        $expect[] = $this->_token; // <ul>
        $expect[] = $this->_token . "bar" . $this->_token;
        $expect[] = $this->_token . "baz" . $this->_token;
        $expect[] = $this->_token . $this->_token; // </ul></li>
        $expect[] = $this->_token . "dib";
        $expect[] = $this->_token; // another <ul>
        $expect[] = $this->_token . "zim" . $this->_token;
        $expect[] = $this->_token . "gir" . $this->_token;
        $expect[] = $this->_token . $this->_token; // </ul></li>
        $expect[] = $this->_token; // </ol>
        $expect = implode('\s*', $expect);
        
        $actual = $this->_rule->parse($source);
        $this->assertRegex($actual, "/$expect/");
    }
    
    public function testRender()
    {
        $source[] = "1. foo";
        $source[] = "\t* bar";
        $source[] = "\t* baz";
        $source[] = "2. dib";
        $source[] = "\t* zim";
        $source[] = "\t* gir";
        $source = implode("\n", $source). "\n\n";
        
        $expect = array();
        $expect[] = "<ol>";
        $expect[] = "<li>foo";
        $expect[] = "<ul>";
        $expect[] = "<li>bar</li>";
        $expect[] = "<li>baz</li>";
        $expect[] = "</ul></li>";
        $expect[] = "<li>dib";
        $expect[] = "<ul>";
        $expect[] = "<li>zim</li>";
        $expect[] = "<li>gir</li>";
        $expect[] = "</ul></li>";
        $expect[] = "</ol>";
        $expect = implode('\s*', $expect);
        
        $result = $this->_rule->parse($source);
        $actual = $this->_rule->render($result);
        $this->assertRegex($actual, '{' . $expect . '}');
    }
}
?>