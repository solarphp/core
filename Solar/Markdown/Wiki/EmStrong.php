<?php
/**
 * 
 * Span plugin to insert emphasis and strong tags.
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
 * @version $Id: EmStrong.php 1655 2006-08-15 00:47:11Z pmjones $
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
 * * `*foo*` and `_foo_` become `<em>foo</em>`.
 * 
 * * `**bar**` and `__bar__` become `<strong>bar</strong>`.
 * 
 * * `***zim***` and `___zim___` become `<strong><em>zim</em></strong>`.
 * 
 * * `**_zim_**` and `__*zim*__` become `<strong><em>zim</em></strong>`.
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