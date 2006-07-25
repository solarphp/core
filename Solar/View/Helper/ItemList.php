<?php
/**
 * 
 * Helper for <ul>, <ol>, and <li> tags.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Meta.php 1524 2006-07-21 17:21:02Z pmjones $
 * 
 */

/**
 * Solar_View_Helper
 */
Solar::loadClass('Solar_View_Helper');
 
/**
 * 
 * Helper for meta tags.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_ItemList extends Solar_View_Helper {
    
    /**
     * 
     * Returns a list of ol, ul, or dl items.
     * 
     * @param string $type The list type, 'ul' or 'ol'.  Default
     * is 'ul'.
     * 
     * @param array $items An array of items in the list.
     * 
     * @param array $attribs Attributes for the <ul> or <ol> tag.
     * 
     * @return string The <ul> or <ol> block of <li> items.
     * 
     */
    public function itemList($type, $items, $attribs = null)
    {
        $type = strtolower($type);
        if ($type == "ol") {
            // ordered
            $begin = "<ol" . $this->_view->attribs($attribs) . ">\n";
            $end = "</ol>\n";
        } elseif ($type == "dl") {
            // definitions
            $begin = "<dl" . $this->_view->attribs($attribs) . ">\n";
            $end = "</dl>\n";
        } else {
            // unordered
            $begin = "<ul" . $this->_view->attribs($attribs) . ">\n";
            $end = "</ul>\n";
        }
        
        $list = '';
        if ($type == 'dl') {
            // definition list
            foreach ((array) $items as $term => $defn) {
                $list .= "    <dt>" . $this->_view->escape($term) . "</dt>\n"
                       . "    <dd>" . $this->_view->escape($defn) . "</dd>\n";
            }
        } else {
            // sequential list
            foreach ((array) $items as $item) {
                $list .= "    <li>" . $this->_view->escape($item) . "</li>\n";
            }
        }
        
        return $begin . $list . $end;
    }
}
?>