<?php
/**
 * 
 * Helper for lists of action links.
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
 * Helper for lists of action links.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 */
class Solar_View_Helper_ActionList extends Solar_View_Helper {
    
    /**
     * 
     * Default display options.
     * 
     * Keys are:
     * 
     * `list_type`:
     * (string) Default 'ul', optionally 'ol'.
     * 
     * `list_class`:
     * (string) Class for the list block.
     * 
     * `item_class`:
     * (string) Class for each unselected item.
     * 
     * `selected_class`:
     * (string) Class for the selected item.
     * 
     * @var array
     * 
     */
    protected $_Solar_View_Helper_ActionList = array(
        'display' => array(
            'list_type'      => 'ul',
            'list_class'     => null,
            'item_class'     => null,
            'selected_class' => null,
        )
    );
    
    /**
     * 
     * Helper for lists of action links.
     * 
     * @param array $items An array of list items in href => text format.
     * 
     * @param string $action The current (selected) action href in the
     * list items.
     * 
     * @param array $display Additional options for building the list.
     * 
     * @return string The list of action items.
     * 
     */
    public function actionList($items, $action = null, $display = null)
    {
        $display = array_merge($this->_config['display'], $display);
        
        // $list_type
        if ($display['list_type'] == 'ol') {
            $list_type = 'ol';
        } else {
            $list_type = 'ul';
        }
        
        // $list_class
        if ($display['list_class']) {
            $list_class = $this->_view->attribs(
                array('class' => $display['list_class'])
            );
        } else {
            $list_class = null;
        }
        
        // $item_class
        if ($display['item_class']) {
            $item_class = $this->_view->attribs(
                array('class' => $display['item_class'])
            );
        } else {
            $item_class = null;
        }
        
        // $selected_class
        if ($display['selected_class']) {
            $selected_class = $this->_view->attribs(
                array('class' => $display['selected_class'])
            );
        } else {
            $selected_class = null;
        }
        
        // build the basic list
        $list = array();
        foreach ($items as $href => $text) {
            if (trim($href, '/') == trim($action, '/')) {
                // selected
                $list[] = "    <li$selected_class>"
                        . $this->_view->escape($text)
                        . "</li>";
            } else {
                // not selected
                $list[] = "    <li$item_class>"
                        . $this->_view->action($href, $text)
                        . "</li>";
            }
        }
        
        // done!
        return "<$list_type$list_class>\n"
             . implode("\n", $list) . "\n"
             . "</$list_type>\n";
    }
}
?>