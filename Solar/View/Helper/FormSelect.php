<?php
/**
 * 
 * Helper for 'select' list of options.
 * 
 * @category Solar
 * 
 * @package Solar_View_Helper_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
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
        $this->_prepare($info);
        
        // force $this->_value to array so we can compare multiple values
        // to multiple options.
        settype($this->_value, 'array');
        
        // check for multiple attrib and change name if needed
        if (isset($this->_attribs['multiple']) &&
            $this->_attribs['multiple'] == 'multiple' &&
            substr($this->_name, -2) != '[]') {
            $this->_name .= '[]';
        }
        
        // check for multiple implied by the name
        if (substr($this->_name, -2) == '[]') {
            // set multiple attrib
            $this->_attribs['multiple'] = 'multiple';
            // if no value is selected, the element won't be sent back to the
            // server at all (like an unchecked checkbox).  add a default
            // blank value under a non-array name so that if no values are
            // selected, an empty value is sent back to the server.
            $xhtml = $this->_view->formHidden(array(
                'name'  => substr($this->_name, 0, -2),
                'value' => null,
            ));
        } else {
            // not multiple, start with blank xhtml
            $xhtml = '';
        }
        
        // build the list of options
        $list = array();
        foreach ($this->_options as $opt_value => $opt_label) {
            $selected = '';
            if (in_array($opt_value, $this->_value)) {
                $selected = ' selected="selected"';
            }
            $list[] = '<option'
                    . ' value="' . $this->_view->escape($opt_value) . '"'
                    . ' label="' . $this->_view->escape($opt_label) . '"'
                    . $selected
                    . '>' . $this->_view->escape($opt_label) . "</option>";
        }
        
        // build and return the remaining xhtml
        return $xhtml
             . '<select name="' . $this->_view->escape($this->_name) . '"'
             . $this->_view->attribs($this->_attribs) . ">\n"
             . "    " . implode("\n    ", $list) . "\n"
             . "</select>";
    }
}
