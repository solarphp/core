<?php
/**
 * 
 * Block class to form tables from Markdown syntax.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 * @author Michel Fortin <http://www.michelf.com/projects/php-markdown/>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Table.php 1721 2006-08-22 18:46:50Z pmjones $
 * 
 */

/**
 * Abstract plugin class.
 */
Solar::loadClass('Solar_Markdown_Extra_Table');

/**
 * 
 * Block class to form tables from Markdown syntax.
 * 
 * Differs from Markdown-Extra in that it aggressive escapes HTML.
 * 
 * Syntax is ...
 * 
 *     |  Header 1  |  Header 2  |  Header N 
 *     | ---------- | ---------- | ----------
 *     | data cell  | data cell  | data cell 
 *     | data cell  | data cell  | data cell 
 *     | data cell  | data cell  | data cell 
 *     | data cell  | data cell  | data cell 
 * 
 * You can force columns alignment by putting a colon in the header-
 * underline row.
 * 
 *     | Left-Aligned |  No Align | Right-Aligned 
 *     | :----------- | --------- | -------------:
 *     | data cell    | data cell | data cell      
 *     | data cell    | data cell | data cell      
 *     | data cell    | data cell | data cell      
 *     | data cell    | data cell | data cell      
 * 
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 */
class Solar_Markdown_Wiki_Table extends Solar_Markdown_Extra_Table {
    
    /**
     * 
     * Support callback for table conversion.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
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
            $text .= "            <th$attr[$n]>". $this->_escape(trim($header)) ."</th>\n";
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
                $text .= "            <td$attr[$n]>". $this->_escape(trim($cell)) ."</td>\n";
            }
            $text .= "        </tr>\n";
        }
        $text .= "    </tbody>\n";
        $text .= "</table>\n";
    
        return $this->_toHtmlToken($text) . "\n";
    }
}
?>