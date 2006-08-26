<?php
class Test_Solar_Markdown extends Solar_Test {
    
    protected $_markdown;
    
    protected $_map = array(
        'test_Amps_and_angle_encoding'                      =>'Amps and angle encoding',
        'test_Auto_links'                                   =>'Auto links',
        'test_Backslash_escapes'                            =>'Backslash escapes',
        'test_Blockquotes_with_code_blocks'                 =>'Blockquotes with code blocks',
        'test_Hard_wrapped_paragraphs_with_list_like_lines' =>'Hard-wrapped paragraphs with list-like lines',
        'test_Horizontal_rules'                             =>'Horizontal rules',
        'test_Inline_HTML_Advanced'                         =>'Inline HTML (Advanced)',
        'test_Inline_HTML_Simple'                           =>'Inline HTML (Simple)',
        'test_Inline_HTML_comments'                         =>'Inline HTML comments',
        'test_Links_inline_style'                           =>'Links, inline style',
        'test_Links_reference_style'                        =>'Links, reference style',
        'test_Literal_quotes_in_titles'                     =>'Literal quotes in titles',
        'test_Markdown_Documentation_Basics'                =>'Markdown Documentation - Basics',
        'test_Markdown_Documentation_Syntax'                =>'Markdown Documentation - Syntax',
        'test_Nested_blockquotes'                           =>'Nested blockquotes',
        'test_Ordered_and_unordered_lists'                  =>'Ordered and unordered lists',
        'test_Strong_and_em_together'                       =>'Strong and em together',
        'test_Tabs'                                         =>'Tabs',
        'test_Tidyness'                                     =>'Tidyness',
    );
    
    /**
     * 
     * Tidy extension must be loaded.  This allows us to ignore
     * tag-level whitepspace differences.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        if (! extension_loaded('tidy')) {
            $this->skip("Tidy extension not loaded");
        }
    }
    public function setup()
    {
        $this->_markdown = Solar::factory('Solar_Markdown');
    }
    
    protected function _output($method)
    {
        $dir = dirname(__FILE__) . '/Markdown/output/';
        $text_file = $dir . $this->_map[$method] . '.text';
        $html_file = $dir . $this->_map[$method] . '.html';
        $source = file_get_contents($text_file);
        
        $expect = $this->_tidy(file_get_contents($html_file));
        $actual = $this->_tidy($this->_markdown->transform($source));
        
        $this->assertSame($actual, $expect);
    }
    
    protected function _tidy($text)
    {
        // tidy up the text
        $tidy = new tidy;
        $tidy->parseString($text, array(), 'utf8');
        $tidy->cleanRepair();
        
        // get only the body portion
        $body = tidy_get_body($tidy);
        return $body->value;
    }
    
    public function test_Amps_and_angle_encoding()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Auto_links()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Backslash_escapes()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Blockquotes_with_code_blocks()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Hard_wrapped_paragraphs_with_list_like_lines()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Horizontal_rules()
    {
        $this->_output(__FUNCTION__);
    }
    
    /**
     * 
     * This test always fails, even in the original implementation.
     * 
     * <http://six.pairlist.net/pipermail/markdown-discuss/2004-December/000909.html>
     * 
     */
    public function test_Inline_HTML_Advanced()
    {
        $this->skip("even perl markdown can't pass this one");
    }
    
    public function test_Inline_HTML_Simple()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Inline_HTML_comments()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Links_inline_style()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Links_reference_style()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Literal_quotes_in_titles()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Markdown_Documentation_Basics()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Markdown_Documentation_Syntax()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Nested_blockquotes()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Ordered_and_unordered_lists()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Strong_and_em_together()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Tabs()
    {
        $this->_output(__FUNCTION__);
    }
    
    public function test_Tidyness()
    {
        $this->_output(__FUNCTION__);
    }
}
?>