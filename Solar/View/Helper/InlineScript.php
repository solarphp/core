<?php
/**
 *
 * Helper for inline JavaScript blocks.
 *
 * @category Solar
 *
 * @package Solar_View
 *
 * @author Paul M. Jones <pmjones@solarphp.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id$
 *
 */

/**
 * Solar_View_Helper
 */
Solar::loadClass('Solar_View_Helper');

/**
 *
 * Helper for inline JavaScript blocks.
 *
 * @category Solar
 *
 * @package Solar_View
 *
 */
class Solar_View_Helper_InlineScript extends Solar_View_Helper {


    /**
     *
     * Returns a <script></script> block that properly commented for inclusion
     * in XHTML documents.
     *
     * @param string $src The source of the script.
     *
     * @param array $attribs Additional attributes for the <script> tag.
     *
     * @return string The <script></script> tag.
     *
     * @see http://developer.mozilla.org/en/docs/Properly_Using_CSS_and_JavaScript_in_XHTML_Documents
     *
     */
    public function inlineScript($src, $attribs = null)
    {
        settype($attribs, 'array');
        unset($attribs['src']);

        if (empty($attribs['type'])) {
            $attribs['type'] = 'application/javascript';
        }

        return '<script'
             . $this->_view->attribs($attribs) . ">\n"
             . "//<![CDATA[\n"
             . trim($src)
             . "\n//]]>\n"
             . "</script>\n";
    }
}
