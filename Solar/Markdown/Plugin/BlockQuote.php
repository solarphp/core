<?php
Solar::loadClass('Solar_Markdown_Plugin');
class Solar_Markdown_Plugin_BlockQuote extends Solar_Markdown_Plugin {
    
    protected $_is_block = true;
    
    /**
     * 
     * Makes <blockquote> blocks.
     * 
     * @param string $text Portion of the Markdown source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        $text = preg_replace_callback('/
            (                       # Wrap whole match in $1
                (                 
                    ^[ \t]*>[ \t]?  # ">" at the start of a line
                    .+\n            # rest of the first line
                    (.+\n)*         # subsequent consecutive lines
                    \n*             # blanks
                )+
            )/xm',
            array($this, '_parse'),
            $text
        );

        return $text;
    }
    
    /**
     * 
     * Support callback for block quotes.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        $bq = $matches[1];
        
        // trim one level of quoting - trim whitespace-only lines
        $bq = preg_replace(array('/^[ \t]*>[ \t]?/m', '/^[ \t]+$/m'), '', $bq);
        
        // recursively parse for blocks inside block-quotes, including
        // other block-quotes
        $bq = $this->_processBlocks($bq);
        $bq = preg_replace('/^/m', "  ", $bq);
        
        // These leading spaces screw with <pre> content, so we need to
        // fix that:
        $bq = preg_replace_callback(
            '{(\s*<pre>.+?</pre>)}sx', 
            array($this, '_trimPreSpaces'),
            $bq
        );

        return $this->_tokenize("<blockquote>") . "\n"
             . $bq
             . $this->_tokenize("</blockquote>")
             . "\n\n";
    }
    
    protected function _trimPreSpaces($matches) {
        $pre = $matches[1];
        $pre = preg_replace('/^  /m', '', $pre);
        return $pre;
    }
}
?>