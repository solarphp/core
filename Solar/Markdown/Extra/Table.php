<?php
Solar::loadClass('Solar_Markdown_Plugin');
class Solar_Markdown_Extra_Table extends Solar_Markdown_Plugin {
    
    protected $_is_block = true;
    
    //
    // Form HTML tables.
    //
    public function parse($text)
    {
        $less_than_tab = $this->_getTabWidth() - 1;
        
        // Find tables with leading pipe.
        //
        //    | Header 1 | Header 2
        //    | -------- | --------
        //    | Cell 1   | Cell 2
        //    | Cell 3   | Cell 4
        // 
        $text = preg_replace_callback('
            {
                ^                               # Start of a line
                [ ]{0,'.$less_than_tab.'}       # Allowed whitespace.
                [|]                             # Optional leading pipe (present)
                (.+) \n                         # $1: Header row (at least one pipe)
                                            
                [ ]{0,'.$less_than_tab.'}       # Allowed whitespace.
                [|] ([ ]*[-:]+[-| :]*) \n       # $2: Header underline
                                            
                (                               # $3: Cells
                    (?:                     
                        [ ]*                    # Allowed whitespace.
                        [|] .* \n               # Row content.
                    )*                      
                )                           
                (?=\n|\Z)                       # Stop at final double newline.
            }xm',
            array($this, '_parsePipe'),
            $text
        );
    
        //
        // Find tables without leading pipe.
        //
        //    Header 1 | Header 2
        //    -------- | --------
        //    Cell 1   | Cell 2
        //    Cell 3   | Cell 4
        //
        $text = preg_replace_callback('
            {
                ^                               # Start of a line
                [ ]{0,'.$less_than_tab.'}       # Allowed whitespace.
                (\S.*[|].*) \n                  # $1: Header row (at least one pipe)
                                                
                [ ]{0,'.$less_than_tab.'}       # Allowed whitespace.
                ([-:]+[ ]*[|][-| :]*) \n        # $2: Header underline
                                                
                (                               # $3: Cells
                    (?:                         
                        .* [|] .* \n            # Row content
                    )*                          
                )                               
                (?=\n|\Z)                       # Stop at final double newline.
            }xm',
            array($this, '_parsePlain'),
            $text
        );

        return $text;
    }
    
    protected function _parsePipe($matches)
    {
        // Remove leading pipe for each row.
        $matches[3]    = preg_replace('/^ *[|]/m', '', $matches[3]);
        return $this->_parsePlain($matches);
    }
    
    
    protected function _parsePlain($matches)
    {
        $head       = $matches[1];
        $underline  = $matches[2];
        $content    = $matches[3];

        // Remove any tailing pipes for each line.
        $head       = preg_replace('/[|] *$/m', '', $head);
        $underline  = preg_replace('/[|] *$/m', '', $underline);
        $content    = preg_replace('/[|] *$/m', '', $content);
    
        // Reading alignment from header underline.
        $separators = preg_split('/ *[|] */', $underline);
        $attr = array();
        foreach ($separators as $n => $s) {
            if (preg_match('/^ *-+: *$/', $s)) {
                $attr[$n] = ' align="right"';
            } elseif (preg_match('/^ *:-+: *$/', $s)) {
                $attr[$n] = ' align="center"';
            } elseif (preg_match('/^ *:-+ *$/', $s)) {
                $attr[$n] = ' align="left"';
            } else {
                $attr[$n] = '';
            }
        }
    
        // handle all spans at once, not just code spans
        $head      = $this->_processSpans($head);
        $headers   = preg_split('/ *[|] */', $head);
        $col_count = count($headers);
    
        // Write column headers.
        $text = "\n<table>\n";
        $text .= "    <thead>\n";
        $text .= "        <tr>\n";
        foreach ($headers as $n => $header) {
            $text .= "            <th$attr[$n]>". trim($header) ."</th>\n";
        }
        $text .= "        </tr>\n";
        $text .= "    </thead>\n";
    
        // Split content by row.
        $rows = explode("\n", trim($content, "\n"));
    
        $text .= "    <tbody>\n";
        foreach ($rows as $row) {
            // handle all spans at once, not just code spans
            $row = $this->_processSpans($row);
        
            // Split row by cell.
            $row_cells = preg_split('/ *[|] */', $row, $col_count);
            $row_cells = array_pad($row_cells, $col_count, '');
        
            $text .= "        <tr>\n";
            foreach ($row_cells as $n => $cell) {
                $text .= "            <td$attr[$n]>". trim($cell) ."</td>\n";
            }
            $text .= "        </tr>\n";
        }
        $text .= "    </tbody>\n";
        $text .= "</table>\n";
    
        return $this->_toHtmlToken($text) . "\n";
    }
}
?>