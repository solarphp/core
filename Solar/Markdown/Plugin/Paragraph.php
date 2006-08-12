<?php
Solar::loadClass('Solar_Markdown_Plugin');
class Solar_Markdown_Plugin_Paragraph extends Solar_Markdown_Plugin {
    
    protected $_is_block = true;
    
    /**
     * 
     * Forms paragraphs from source text.
     * 
     * @param string $text Portion of the Markdown source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {   
        // Strip leading and trailing lines:
        $text = preg_replace(array('/\A\n+/', '/\n+\z/'), '', $text);
        
        // split into possible paragraphs
        $grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        // Wrap <p> tags around apparent paragraphs.
        foreach ($grafs as $key => $value) {
            if (! $this->_config['markdown']->isHtmlToken($value)) {
                $value = $this->_processSpans($value);
                $value = preg_replace('/^([ \t]*)/', '<p>', $value);
                $value .= "</p>";
                $grafs[$key] = $value;
            }
        }
        
        /* // WHY DO THIS?
         * 
        // Unhashify HTML blocks
        foreach ($grafs as $key => $value) {
            if (isset( $this->_html_blocks[$value] )) {
                $grafs[$key] = $this->_html_blocks[$value];
            }
        }
         * 
         */
        
        return implode("\n\n", $grafs);
    }
}
?>