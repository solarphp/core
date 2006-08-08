<?php
Solar::loadClass('Solar_Markdown_Rule');
class Solar_Markdown_Rule_Prefilter extends Solar_Markdown_Rule {
    
    /**
     * 
     * Pre-filters the source text before any parsing occurs.
     * 
     * @param string $text The source text.
     * 
     * @return string $text The text after being filtered.
     * 
     */
    public function filter($text)
    {
        // Standardize line endings
        $text = str_replace(array("\r\n", "\r"), "\n", $text);

        // Make sure $text ends with a couple of newlines:
        $text .= "\n\n";

        // Convert tabs to spaces in a surprisingly nice-looking way.
        $text = $this->_tabsToSpaces($text);

        // Strip any lines consisting only of spaces and tabs.
        // This makes subsequent regexen easier to write, because we can
        // match consecutive blank lines with /\n+/ instead of something
        // contorted like /[ \t]*\n+/ .
        $text = preg_replace('/^[ \t]+$/m', '', $text);
        
        // done
        return $text;
    }
    
    /**
     * 
     * Returns the text as-is.
     * 
     * @param string $text The source text.
     * 
     * @return string The identical text.
     * 
     */
    public function parse($text)
    {
        return $text;
    }
    
    /**
     * 
     * Replaces tabs with the appropriate number of spaces.
     *
     * <http://www.mail-archive.com/macperl-anyperl@perl.org/msg00144.html>
     * 
     * > It will take into account the length of the string before the tab
     * > starting from the start of the string, from the previous newline, or
     * > from the last replaced tab; and pad with 1 to 4 spaces so the string
     * > length becomes the next multiple of 4.
     * 
     * @param string $text A block of text with tabs.
     * 
     * @return string The same block of text, with tabs converted to 
     * spaces so that columns still line up.
     * 
     */
    protected function _tabsToSpaces($text)
    {
        // For each line we separate the line in blocks delemited by
        // tab characters. Then we reconstruct every line by adding the 
        // appropriate number of space between each blocks.
        $lines = explode("\n", $text);
        $text = "";
    
        foreach ($lines as $line) {
            // Split in blocks.
            $blocks = explode("\t", $line);
            // Add each blocks to the line.
            $line = $blocks[0];
            unset($blocks[0]); # Do not add first block twice.
            foreach ($blocks as $block) {
                // Calculate amount of space, insert spaces, insert block.
                $amount = $this->_tab_width - strlen($line) % $this->_tab_width;
                $line .= str_repeat(" ", $amount) . $block;
            }
            $text .= "$line\n";
        }
        return $text;
    }
}
?>