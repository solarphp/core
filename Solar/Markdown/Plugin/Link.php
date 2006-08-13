<?php
Solar::loadClass('Solar_Markdown_Plugin');
class Solar_Markdown_Plugin_Link extends Solar_Markdown_Plugin {
    
    protected $_is_span = true;
    
    protected $_chars = '[]()"\'';
    
    # Turn Markdown link shortcuts into XHTML <a> tags.
    public function parse($text)
    {
        // First, handle reference-style links: [link text] [id]
        $text = preg_replace_callback("{
                (                                    # wrap whole match in $1
                  \\[
                    (".$this->_nested_brackets.")    # link text = $2
                  \\]

                  [ ]?                               # one optional space
                  (?:\\n[ ]*)?                       # one optional newline followed by spaces
                                                 
                  \\[                            
                    (.*?)                            # id = $3
                  \\]
                )
            }xs",
            array($this, '_parseReference'),
            $text
        );

        // Next, inline-style links: [link text](url "optional title")
        $text = preg_replace_callback("{
                (                                   # wrap whole match in $1
                  \\[                           
                    (".$this->_nested_brackets.")    # link text = $2
                  \\]                           
                  \\(                               # literal paren
                    [ \\t]*                     
                    <?(.*?)>?                       # href = $3
                    [ \\t]*                     
                    (                               # $4
                      (['\"])                       # quote char = $5
                      (.*?)                         # Title = $6
                      \\5                           # matching quote
                    )?                              # title is optional
                  \\)
                )
            }xs",
            array($this, '_parseInline'),
            $text
        );

        return $text;
    }
    
    protected function _parseReference($matches)
    {
        $whole_match = $matches[1];
        $alt_text    = $matches[2];
        $name        = strtolower(trim($matches[3]));

        if (empty($name)) {
            // for shortcut links like [this][].
            $name = strtolower($alt_text);
        }
        
        $link = $this->_config['markdown']->getLink($name);
        if ($link) {
            
            $href = $this->_escape($link['href']);
            $result = "<a href=\"$href\"";
            
            if ($link['title']) {
                $title = $this->_escape($link['title']);
                $result .=  " title=\"$title\"";
            }
            
            $result .= ">" . $this->_escape($alt_text) . "</a>";
            
            // encode special Markdown characters
            $result = $this->_encode($result);
            
        } else {
            $result = $whole_match;
        }
        
        return $result;
    }
    
    
    function _parseInline($matches)
    {
        $alt_text = $this->_escape($matches[2]);
        $href     = $this->_escape($matches[3]);

        $result   = "<a href=\"$href\"";
        
        if (! empty($matches[6])) {
            $title = $this->_escape($matches[6]);
            $result .=  " title=\"$title\"";
        }
    
        $result .= ">$alt_text</a>";
        
        // encode special Markdown characters
        $result = $this->_encode($result);

        return $result;
    }
}
?>