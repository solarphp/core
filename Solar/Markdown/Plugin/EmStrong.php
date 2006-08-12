<?php
Solar::loadClass('Solar_Markdown_Plugin');
class Solar_Markdown_Plugin_EmStrong extends Solar_Markdown_Plugin {
    
    protected $_is_span = true;
    
    protected $_chars = '*_';
    
    public function parse($text)
    {
        # <strong> must go first:
        $text = preg_replace_callback('{
                (                                       # $1: Marker
                    (?<!\*\*) \*\* |                    #     (not preceded by two chars of
                    (?<!__)   __                        #      the same marker)
                )                       
                (?=\S)                                  # Not followed by whitespace 
                (?!\1)                                  #   or two others marker chars.
                (                                       # $2: Content
                    (?:                 
                        [^*_]+?                         # Anthing not em markers.
                    |
                                                        # Balance any regular emphasis inside.
                        ([*_]) (?=\S) .+? (?<=\S) \3    # $3: em char (* or _)
                    |                                   
                        (?! \1 ) .                      # Allow unbalanced * and _.
                    )+?                                 
                )                                       
                (?<=\S) \1                              # End mark not preceded by whitespace.
            }sx',
            array($this, '_parseStrong'),
            $text
        );

        # Then <em>:
        $text = preg_replace_callback(
            '{ ( (?<!\*)\* | (?<!_)_ ) (?=\S) (?! \1) (.+?) (?<=\S) \1 }sx',
            array($this, '_parseEm'),
            $text
        );

        return $text;
    }
    
    protected function _parseStrong($matches)
    {
        return "<strong>$matches[2]</strong>";
    }
    
    protected function _parseEm($matches)
    {
        return "<em>$matches[2]</em>";
    }
}
?>