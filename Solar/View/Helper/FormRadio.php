<?php
/**
 * 
 * Helper for a set of radio button elements.
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
class Solar_View_Helper_FormRadio extends Solar_View_Helper_FormElement
{
    protected $_Solar_View_Helper_FormRadio = array(
        'label_class' => 'radio',
    );
    
    /**
     * 
     * Generates a set of radio button elements.
     * 
     * @param array $info An array of element information.
     * 
     * @return string The element XHTML.
     * 
     */
    public function formRadio($info)
    {
        $this->_prepare($info);
        $radios = array();
        
        // default value if none are checked.
        $radios[] = $this->_view->formHidden(array('name' => $this->_name, 'value' => null));
        
        // count for ID suffixes
        $i = 0;
        
        // add radio buttons.
        foreach ($this->_options as $opt_value => $opt_label) {
        
            // is it checked?
            if ($opt_value == $this->_value) {
                $this->_attribs['checked'] = 'checked';
            } else {
                unset($this->_attribs['checked']);
            }
            
            // build an incremented ID cleanly from original attribs
            $radio_attribs = $this->_attribs;
            if (! empty($attribs['id'])) {
                $i++;
                $radio_attribs['id'] .= "-{$i}";
            }
            
            // put a class on the label?
            if ($this->_config['label_class']) {
                $label_attribs = $this->_view->attribs(array(
                    'class' => $this->_config['label_class']
                ));
            } else {
                $label_attribs = null;
            }
            
            // build the radio button
            $radios[] = "<label{$label_attribs}>"
                      . '<input type="radio"'
                      . ' name="' . $this->_view->escape($this->_name) . '"'
                      . ' value="' . $this->_view->escape($opt_value) . '"'
                      . $this->_view->attribs($radio_attribs) . ' /> '
                      . $this->_view->escape($opt_label) . '</label>';
        }
        
        return implode("\n", $radios);
    }
}
