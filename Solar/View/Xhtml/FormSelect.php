<?php
/**
 * 
 * Helper for 'select' list of options.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id: Solar_View_Helper_FormSelect.php 654 2006-01-11 17:10:06Z pmjones $
 * 
 */

/**
 * The abstract FormElement class.
 */
require_once 'Solar/View/Xhtml/FormElement.php';

/**
 * 
 * Helper for 'select' list of options.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * 
 */
class Solar_View_Helper_FormSelect extends Solar_View_Helper_FormElement {
    
    /**
     * 
     * Generates 'select' list of options.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formSelect($info)
    {
        extract($this->_info($info));
        $xhtml = '';
        
        // force $value to array so we can compare multiple values
        // to multiple options.
        settype($value, 'array');
        
        // check for multiple attrib and change name if needed
        if (isset($attribs['multiple']) &&
            $attribs['multiple'] == 'multiple' &&
            substr($name, -2) != '[]') {
            $name .= '[]';
        }
        
        // check for multiple implied by the name and set attrib if
        // needed
        if (substr($name, -2) == '[]') {
            $attribs['multiple'] = 'multiple';
        }
        
        // now start building the XHTML.
        if ($disable) {
        
            // disabled.
            // generate a plain list of selected options.
            // show the label, not the value, of the option.
            $list = array();
            foreach ($options as $opt_value => $opt_label) {
                if (in_array($opt_value, $value)) {
                    // add the hidden value
                    $opt = $this->_view->formHidden(array('name' => $name, 'value' => $opt_value));
                    // add the display label
                    $opt .= $this->_view->escape($opt_label);
                    // add to the list
                    $list[] = $opt;
                }
            }
            $xhtml .= implode($listsep, $list);
            
        } else {
        
            // enabled.
            // the surrounding select element first.
            $xhtml .= '<select';
            $xhtml .= ' name="' . $this->_view->escape($name) . '"';
            $xhtml .= $this->_view->attribs($attribs);
            $xhtml .= ">\n    ";
            
            // build the list of options
            $list = array();
            foreach ($options as $opt_value => $opt_label) {
                $opt = '<option';
                $opt .= ' value="' . $this->_view->escape($opt_value) . '"';
                $opt .= ' label="' . $this->_view->escape($opt_label) . '"';
                if (in_array($opt_value, $value)) {
                    $opt .= ' selected="selected"';
                }
                $opt .= '>' . $this->_view->escape($opt_label) . "</option>";
                $list[] = $opt;
            }
            
            // add the options to the xhtml
            $xhtml .= implode("\n    ", $list);
            
            // finish up
            $xhtml .= "\n</select>";
            
        }
        
        return $xhtml;
    }
}
?>