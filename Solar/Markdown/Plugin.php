<?php
abstract class Solar_Markdown_Plugin extends Solar_Base {
    
    protected $_Solar_Markdown_Plugin = array(
        // the "parent" markdown object
        'markdown' => null,
    );
    
    // characters this plugin uses for parsing, which should be
    // escaped by other plugins.
    protected $_chars = '';
    
    // for nested brackets
    protected $_nested_brackets_depth = 6;
    
    // the regex for nested brackets
    protected $_nested_brackets = '';
    
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_nested_brackets = 
            str_repeat('(?>[^\[\]]+|\[', $this->_nested_brackets_depth).
            str_repeat('\])*', $this->_nested_brackets_depth);
        
    }

    /**
     * 
     * Is this a block-level plugin?
     * 
     * (It is possible for a plugin to be neither block nor span.)
     * 
     * @var bool
     * 
     */
    protected $_is_block = false;
    
    /**
     * 
     * Is this a span-level plugin?
     * 
     * (It is possible for a plugin to be neither block nor span.)
     * 
     * @var bool
     * 
     */
    protected $_is_span = false;
    
    /**
     * 
     * Is this a block-level plugin?
     * 
     * Reports the value of $this->_is_block.
     * 
     * @var bool
     * 
     */
    public function isBlock()
    {
        return (bool) $this->_is_block;
    }
    
    /**
     * 
     * Is this a span-level plugin?
     * 
     * Reports the value of $this->_is_span.
     * 
     * @var bool
     * 
     */
    public function isSpan()
    {
        return (bool) $this->_is_span;
    }
    
    /**
     * 
     * Get the list of characters this plugin uses for parsing.
     * 
     * @return string
     * 
     */
    public function getChars()
    {
        return $this->_chars;
    }
    
    /**
     * 
     * Resets this plugin to its original state (for multiple parsings).
     * 
     * @return void
     * 
     */
    public function reset()
    {
    }
    
    /**
     * 
     * Prepares the source text before any parsing occurs.
     * 
     * Returns the text as-is.
     * 
     * @param string $text The source text.
     * 
     * @return string $text The text after being filtered.
     * 
     */
    public function prepare($text)
    {
        return $text;
    }
    
    /**
     * 
     * Parses the source text using the regular expression.
     * 
     * @param string $text The source text.
     * 
     * @return string The text after parsed values have been replaced
     * with delimited tokens.
     * 
     */
    public function parse($text)
    {
        return $text;
    }
     
    /**
     * 
     * Cleans up the source text after all parsing occurs.
     * 
     * Returns the text as-is.
     * 
     * @param string $text The source text.
     * 
     * @return string $text The text after being filtered.
     * 
     */
    public function cleanup($text)
    {
        return $text;
    }
    
    /**
     * 
     * Removes one level of leading tabs or space from a text block.
     * 
     * E.g., if a block of text is indented by 3 tabs, it will be
     * returned as indented with only 2 tabs.
     * 
     * @param string $text A block of text.
     * 
     * @return string The same text out-dented by one level of tabs
     * or spaces.
     * 
     */
    protected function _outdent($text)
    {
        $tab_width = $this->_getTabWidth();
        return preg_replace(
            "/^(\\t|[ ]{1,$tab_width})/m",
            "",
            $text
        );
    }
    
    /**
     * 
     * Escapes HTML in source text.
     * 
     * @param string $text Source text.
     * 
     * @return string The escaped text.
     * 
     */
    protected function _escape($text)
    {
        return $this->_config['markdown']->escape($text);
    }
    
    /**
     * 
     * Escapes special Markdown characters.
     * 
     * @param string $text Source text.
     * 
     * @return string The escaped text.
     * 
     */
    protected function _encode($text)
    {
        return $this->_config['markdown']->encode($text);
    }
    
    /**
     * 
     * Uses the "parent" Markdown object to parse blocks.
     * 
     * @param string $text Source text.
     * 
     * @return string The source text after block parsing.
     * 
     */
    protected function _processBlocks($text)
    {
        return $this->_config['markdown']->processBlocks($text);
    }
    
    /**
     * 
     * Uses the "parent" Markdown object to parse spans.
     * 
     * @param string $text Source text.
     * 
     * @return string The source text after span parsing.
     * 
     */
    protected function _processSpans($text)
    {
        return $this->_config['markdown']->processSpans($text);
    }
    
    
    /**
     * 
     * Returns the number of spaces per tab.
     * 
     * @return int
     * 
     */
    protected function _getTabWidth()
    {
        return $this->_config['markdown']->getTabWidth();
    }
}
?>