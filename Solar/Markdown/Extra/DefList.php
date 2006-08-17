<?php
Solar::loadClass('Solar_Markdown_Plugin');
class Solar_Markdown_Extra_DefList extends Solar_Markdown_Plugin {
    
    protected $_is_block = true;
    
    //
    // Form HTML definition lists.
    //
    public function parse($text)
    {
        $less_than_tab = $this->_getTabWidth() - 1;

        // Re-usable pattern to match any entire dl list:
        $whole_list = '
            (                                               # $1 = whole list
              (                                             # $2
                [ ]{0,'.$less_than_tab.'}                   
                ((?>.*\S.*\n)+)                             # $3 = defined term
                \n?                                         
                [ ]{0,'.$less_than_tab.'}:[ ]+              # colon starting definition
              )                                             
              (?s:.+?)                                      
              (                                             # $4
                  \z                                        
                |                                           
                  \n{2,}                                    
                  (?=\S)                                    
                  (?!                                       # Negative lookahead for another term
                    [ ]{0,'.$less_than_tab.'}               
                    (?: \S.*\n )+?                          # defined term
                    \n?                                     
                    [ ]{0,'.$less_than_tab.'}:[ ]+          # colon starting definition
                  )                                         
                  (?!                                       # Negative lookahead for another definition
                    [ ]{0,'.$less_than_tab.'}:[ ]+          # colon starting definition
                  )
              )
            )
        '; // mx

        $text = preg_replace_callback(
            '{
                (?:(?<=\n\n)|\A\n?)
                ' . $whole_list . '
            }mx',
            array($this, '_parse'),
            $text
        );

        return $text;
    }
    
    protected function _parse($matches)
    {
        // Re-usable patterns to match list item bullets and number markers:
        $list = $matches[1];
    
        // Turn double returns into triple returns, so that we can make a
        // paragraph for the last item in a list, if necessary:
        $result = trim($this->_processItems($list));
        $result = "<dl>\n" . $result . "\n</dl>";
        return $this->_toHtmlToken($result) . "\n\n";
    }


    //
    //    Process the contents of a single ordered or unordered list, splitting it
    //    into individual list items.
    //
    protected function _processItems($list_str)
    {
        $less_than_tab = $this->_getTabWidth() - 1;
    
        // trim trailing blank lines:
        $list_str = preg_replace("/\n{2,}\\z/", "\n", $list_str);

        // Process definition terms.
        $list_str = preg_replace_callback(
            '{
                (?:\n\n+|\A\n?)                                 # leading line
                (                                               # definition terms = $1
                    [ ]{0,'.$less_than_tab.'}                   # leading whitespace
                    (?![:][ ]|[ ])                              # negative lookahead for a definition 
                                                                # mark (colon) or more whitespace.
                    (?: \S.* \n)+?                              # actual term (not whitespace).    
                )                                               
                (?=\n?[ ]{0,3}:[ ])                             # lookahead for following line feed 
                                                                # with a definition mark.
            }xm',
            array($this, '_processDt'),
            $list_str
        );

        // Process actual definitions.
        $list_str = preg_replace_callback(
            '{
                \n(\n+)?                                        # leading line = $1
                [ ]{0,'.$less_than_tab.'}                       # whitespace before colon
                [:][ ]+                                         # definition mark (colon)
                ((?s:.+?))                                      # definition text = $2
                (?= \n+                                         # stop at next definition mark,
                    (?:                                         # next term or end of text
                        [ ]{0,'.$less_than_tab.'} [:][ ]    |
                        <dt> | \z
                    )                        
                )                    
            }xm',
            array($this, '_processDd'),
            $list_str
        );

        return $list_str;
    }
    
    protected function _processDt($matches)
    {
        $terms = explode("\n", trim($matches[1]));
        $text = '';
        foreach ($terms as $term) {
            $term = $this->_processSpans(trim($term));
            $text .= "\n<dt>" . $term . "</dt>";
        }
        return $text . "\n";
    }
    
    protected function _processDd($matches)
    {
        $leading_line = $matches[1];
        $def          = $matches[2];

        if ($leading_line || preg_match('/\n{2,}/', $def)) {
            $def = $this->_processBlocks($this->_outdent($def . "\n\n"));
            $def = "\n". $def ."\n";
        } else {
            $def = rtrim($def);
            $def = $this->_processSpans($this->_outdent($def));
        }

        return "\n<dd>" . $def . "</dd>\n";
    }
}
?>