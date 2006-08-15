<?php
/**
 * 
 * _____
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 * @author John Gruber <http://daringfireball.net/projects/markdown/>
 * 
 * @author Michel Fortin <http://www.michelf.com/projects/php-markdown/>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Abstract plugin class.
 */
Solar::loadClass('Solar_Markdown_Plugin');

/**
 * 
 * _____
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 */
class Solar_Markdown_Plugin_Paragraph extends Solar_Markdown_Plugin {
    
    /**
     * 
     * This is a block plugin.
     * 
     * @var bool
     * 
     */
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
            if (! $this->_isHtmlToken($value)) {
                $value = $this->_processSpans($value);
                $value = preg_replace('/^([ \t]*)/', '<p>', $value);
                $value .= "</p>";
                $grafs[$key] = $value;
            }
            
            // else {
            //     $grafs[$key] = $this->_unHtmlToken($value);
            // }
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