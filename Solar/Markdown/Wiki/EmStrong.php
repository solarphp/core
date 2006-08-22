<?php
/**
 * 
 * Span plugin to insert emphasis and strong tags.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
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
Solar::loadClass('Solar_Markdown_Extra_EmStrong');

/**
 * 
 * Span plugin to insert emphasis and strong tags.
 * 
 * Works the same as the "Extra" em/strong plugin, in that underscores
 * and stars inside words are not triggers for markup.  However, this
 * plugin goes one better and saves the markup as an HTML token.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 */
class Solar_Markdown_Wiki_EmStrong extends Solar_Markdown_Extra_EmStrong {
    
    /**
     * 
     * Support callback for strong tags.
     * 
     * @param string $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseStrong($matches)
    {
        return $this->_toHtmlToken("<strong>$matches[2]</strong>");
    }
    
    /**
     * 
     * Support callback for em tags.
     * 
     * @param string $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parseEm($matches)
    {
        return $this->_toHtmlToken("<em>$matches[2]</em>");
    }
}
?>