<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Plugin.php';

class Test_Solar_Markdown_Plugin_HeaderAtx extends Test_Solar_Markdown_Plugin {
    
    // should show no changes
    public function testFilter()
    {
        $source = "foo bar baz";
        $expect = $source;
        $actual = $this->_rule->filter($source);
        $this->assertSame($actual, $expect);
    }
    
    public function testParse()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "# 1";
        $source[] = "## 2";
        $source[] = "### 3";
        $source[] = "#### 4";
        $source[] = "##### 5";
        $source[] = "###### 6";
        $source[] = "####### 7";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = $this->_token . "1" . $this->_token . "\n";
        $expect[] = $this->_token . "2" . $this->_token . "\n";
        $expect[] = $this->_token . "3" . $this->_token . "\n";
        $expect[] = $this->_token . "4" . $this->_token . "\n";
        $expect[] = $this->_token . "5" . $this->_token . "\n";
        $expect[] = $this->_token . "6" . $this->_token . "\n";
        $expect[] = $this->_token . "# 7" . $this->_token . "\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_rule->parse($source);
        $this->assertRegex($actual, "/$expect/");
    }
    
    public function testRender()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "# 1";
        $source[] = "## 2";
        $source[] = "### 3";
        $source[] = "#### 4";
        $source[] = "##### 5";
        $source[] = "###### 6";
        $source[] = "####### 7";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = "<h1>1</h1>\n";
        $expect[] = "<h2>2</h2>\n";
        $expect[] = "<h3>3</h3>\n";
        $expect[] = "<h4>4</h4>\n";
        $expect[] = "<h5>5</h5>\n";
        $expect[] = "<h6>6</h6>\n";
        $expect[] = "<h6># 7</h6>\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $result = $this->_rule->parse($source);
        $actual = $this->_rule->render($result);
        
        $this->assertSame($actual, $expect);
    }
    
    public function testParse_trailingHashes()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "# 1 #";
        $source[] = "# 2 ##";
        $source[] = "# 5 ###";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = $this->_token . "1" . $this->_token . "\n";
        $expect[] = $this->_token . "2" . $this->_token . "\n";
        $expect[] = $this->_token . "5" . $this->_token . "\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $actual = $this->_rule->parse($source);
        $this->assertRegex($actual, "/$expect/");
    }
    
    public function testRender_trailingHashes()
    {
        $source = array();
        $source[] = "foo bar";
        $source[] = "# 1 #";
        $source[] = "# 2 ##";
        $source[] = "# 5 ###";
        $source[] = "baz dib";
        $source = implode("\n", $source);
        
        $expect[] = "foo bar";
        $expect[] = "<h1>1</h1>\n";
        $expect[] = "<h1>2</h1>\n";
        $expect[] = "<h1>5</h1>\n";
        $expect[] = "baz dib";
        $expect = implode("\n", $expect);
        
        $result = $this->_rule->parse($source);
        $actual = $this->_rule->render($result);
        
        $this->assertSame($actual, $expect);
    }
    
}
?>