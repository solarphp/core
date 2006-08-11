<?php
Solar::loadClass('Solar_Markdown_Plugin');
class Solar_Markdown_Plugin_HeaderAtx extends Solar_Markdown_Plugin {
    
    protected $_is_block = true;
    
    /**
     * 
     * Turns setext and atx-style headers into XHTML <h?> tags.
     * 
     * @param string $text Portion of the Markdown source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        // atx-style headers:
        //    # Header 1
        //    ## Header 2
        //    ## Header 2 with closing hashes ##
        //    ...
        //    ###### Header 6
        //
        $text = preg_replace_callback(
            "{
                ^(\\#{1,6}) # $1 = string of #'s
                [ \\t]*
                (.+?)       # $2 = Header text
                [ \\t]*
                \\#*        # optional closing #'s (not counted)
                \\n+
            }xm",
            array($this, '_parse'),
            $text
        );
        
        return $text;
    }

    /**
     * 
     * Support callback.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        $tag = 'h' . strlen($matches[1]); // h1, h2, h5, etc
        return "<$tag>"
             . $this->_processSpans($matches[2])
             . "</$tag>"
             . "\n\n";
    }
}
?>