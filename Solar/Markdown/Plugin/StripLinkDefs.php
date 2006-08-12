<?php
Solar::loadClass('Solar_Markdown_Plugin');
class Solar_Markdown_Plugin_StripLinkDefs extends Solar_Markdown_Plugin {
    
    /**
     * 
     * Removes link definitions from source and saves for later use.
     * 
     * @param string $text Markdown source text.
     * 
     * @return string The text without link definitions.
     * 
     */
    public function prepare($text)
    {
        $less_than_tab = $this->_getTabWidth() - 1;

        # Link defs are in the form: ^[id]: url "optional title"
        $text = preg_replace_callback('{
                ^[ ]{0,'.$less_than_tab.'}\[(.+)\]:  # id = $1
                  [ \t]*                             
                  \n?                                # maybe *one* newline
                  [ \t]*                             
                <?(\S+?)>?                           # url = $2
                  [ \t]*                             
                  \n?                                # maybe one newline
                  [ \t]*                             
                (?:                                  
                    (?<=\s)                          # lookbehind for whitespace
                    ["(]                             
                    (.+?)                            # title = $3
                    [")]
                    [ \t]*
                )?    # title is optional
                (?:\n+|\Z)
            }xm',
            array($this, '_prepare'),
            $text
        );
        
        return $text;
    }
    
    protected function _prepare($matches)
    {
        $name  = strtolower($matches[1]);
        $href  = $matches[2];
        $title = empty($matches[3]) ? null : $matches[3];
        
        // save the link
        $this->_config['markdown']->setLink($name, $href, $title);
        
        // done.
        // no return, it's supposed to be removed.
    }
}
?>