<?php
abstract class Solar_Markdown_Plugin extends Solar_Base {
    
    /**
     * 
     * Array of token keys with text values to replace them at
     * rendering time.
     * 
     * @var array
     * 
     */
    protected $_token = array();
    
    /**
     * 
     * The name of this class.
     * 
     * @var string
     * 
     */
    protected $_class = null;
    
    /**
     * 
     * The current token count.
     * 
     * @var int
     * 
     */
    protected $_count = 0;
    
    /**
     * 
     * Number of spaces per tab.
     * 
     * @var int
     * 
     */
    protected $_tab_width = 4;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_class = get_class($this);
    }
    
    /**
     * 
     * Pre-filters the source text before any parsing occurs.
     * 
     * Returns the text as-is.
     * 
     * @param string $text The source text.
     * 
     * @return string $text The text after being filtered.
     * 
     */
    public function filter($text)
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
     * Places tokenized values back into the source text.
     * 
     * @param string $text The source text.
     * 
     * @return string The source text with replaced token values.
     * 
     */
    public function render($text)
    {
        foreach ($this->_token as $key => $val) {
            $text = str_replace(
                $this->_getToken($key),
                $val,
                $text
            );
        }
        
        return $text;
    }
   
    /**
     * 
     * Support callback for processing parsed text.
     * 
     * @param array $matches The matches from the regular expression.
     * 
     * @return string The replacement text for the matches.
     * 
     */
    protected function _parse($matches)
    {
        return $this->_tokenize($matches[0]);
    }
    
    /**
     * 
     * Returns a delimited token representing a piece of text.
     * 
     * @param string $text The text to represent as a token.
     * 
     * @return string A delimited token identifier.
     * 
     */
    protected function _tokenize($text)
    {
        $this->_token[$this->_count] = $text;
        return $this->_getToken($this->_count ++);
    }
    
    /**
     * 
     * Gets the delimited token string for a given token key.
     * 
     * @param int $key The token key number.
     * 
     * @return string The delimited token string for the key.
     * 
     */
    protected function _getToken($key)
    {
        return "\x0E"  // ctrl-n, "shift out"
             . md5($this->_class . ':' . $key)
             . "\x0F"; // ctrl-o, "shift in"
    }
    
    /**
     * 
     * Removes one level of leading tabs or space from a line.
     * 
     * @param string $text A line of text.
     * 
     * @return string The same text without leading whitespace.
     * 
     */
    protected function _outdent($text)
    {
        return preg_replace("/^(\\t|[ ]{1,$this->_tab_width})/m", "", $text);
    }
    
}
?>