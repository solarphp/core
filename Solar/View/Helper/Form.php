<?php
/**
 * 
 * Helper for building CSS-based forms.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * 
 * @version $Id$
 * 
 */

/**
 * Needed for Solar_Form instanceof comparisons.
 */
Solar::loadClass('Solar_Form');

/**
 * Solar_View_Helper
 */
Solar::loadClass('Solar_View_Helper');

/**
 * 
 * Helper for building CSS-based forms.
 * 
 * This is a fluent class; all method calls except fetch() return
 * $this, which means you can chain method calls for easier readability.
 * 
 * @category Solar
 * 
 * @package Solar_View
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 */
class Solar_View_Helper_Form extends Solar_View_Helper {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * @var array
     */
    protected $_config = array(
        'attribs' => array(),
    );
    
    /**
     * 
     * Attributes for the form tag.
     * 
     * @var array
     * 
     */
    protected $_attribs = array();
    
    /**
     * 
     * Collection of form-level feedback messages.
     * 
     * @var array
     * 
     */
    protected $_feedback = array();
    
    /**
     * 
     * Collection of hidden elements.
     * 
     * @var array
     * 
     */
    protected $_hidden = array();
    
    /**
     *
     * Stack of element and layout pieces for the form.
     * 
     * @var array
     * 
     */
    protected $_stack = array();
    
    /**
     *
     * Default form tag attributes.
     * 
     * @var array
     * 
     */
    protected $_default_attribs = array(
        'action'  => null,
        'method'  => 'post',
        'enctype' => 'multipart/form-data',
    );
    
    /**
     *
     * Default info for each element.
     * 
     * @var array
     * 
     */
    protected $_default_info = array(
        'type'     => '',
        'name'     => '',
        'value'    => '',
        'label'    => '',
        'attribs'  => array(),
        'options'  => array(),
        'disable'  => false,
        'require'  => false,
        'feedback' => array(),
    );
    
    /**
     * 
     * Forcibly disable (or enable) elements?
     * 
     * If null, does not affect elements.  If false, all elements are
     * disabled.  If true, all elements are enabled.
     * 
     * @var bool
     * 
     */
    protected $_disable = null;
    
    /**
     *
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        $this->_default_attribs['action'] = $_SERVER['REQUEST_URI'];
        parent::__construct($config);
        $this->reset();
    }
    
    /**
     *
     * Magic __call() for addElement() using element helpers.
     * 
     * Allows $this->elementName() internally, and
     * $this->form()->elementType() externally.
     * 
     * @param string $type The form element type (text, radio, etc).
     * 
     * @param array $args Arguments passed to the method call; only
     * the first argument is used, the $info array.
     * 
     * @return string The form element helper output.
     * 
     */
    public function __call($type, $args)
    {
        $info = $args[0];
        $info['type'] = $type;
        return $this->addElement($info);
    }
    
    /**
     *
     * Main method interface to Solar_View.
     * 
     * @param Solar_Form|array $spec If a Solar_Form object, does a
     * full auto build and fetch of a form based on the Solar_Form
     * properties.  If an array, treated as attribute keys and values
     * for the form tag.
     * 
     * @return string|Solar_View_Helper_Form
     * 
     */
    public function form($spec = null)
    {
        if ($spec instanceof Solar_Form) {
            // auto-build and fetch from a Solar_Form object
            $this->reset();
            $this->auto($spec);
            return $this->fetch();
        } elseif (is_array($spec)) {
            // set attributes from an array
            foreach ($spec as $key => $val) {
                $this->setAttrib($key, $val);
            }
            return $this;
        } else {
            // just return self
            return $this;
        }
    }
    
    /**
     *
     * Sets the default 'disable' flag for elements.
     * 
     * @param bool $flag True to force-enable, false to force-disable, null to
     * leave element 'disable' keys alone.
     * 
     * @return Solar_View_Helper_Form
     * 
     */
    public function disable($flag)
    {
        if ($flag === null) {
            $this->_disable = null;
        } else {
            $this->_flag = (bool) $flag;
        }
        return $this;
    }
    
    /**
     *
     * Sets a form-tag attribute.
     * 
     * @param string $key The attribute name.
     * 
     * @param string $val The attribute value.
     * 
     * @return Solar_View_Helper_Form
     * 
     */
    public function setAttrib($key, $val = null)
    {
        $this->_attribs[$key] = $val;
        return $this;
    }
    
    /**
     *
     * Adds to the form-level feedback message array.
     * 
     * @param string|array $spec The feedback message(s).
     * 
     * @return Solar_View_Helper_Form
     * 
     */
    public function addFeedback($spec)
    {
        $this->_feedback = array_merge($this->_feedback, (array) $spec);
    }
    
    /**
     *
     * Adds a single element to the form.
     * 
     * @param array $info The element information.
     * 
     * @return Solar_View_Helper_Form
     * 
     */
    public function addElement($info)
    {
        $info = array_merge($this->_default_info, $info);
        
        if (empty($info['type'])) {
            throw $this->_exception('ERR_NO_ELEMENT_TYPE');
        }
        
        if (empty($info['name'])) {
            throw $this->_exception('ERR_NO_ELEMENT_NAME');
        }
        
        if (empty($info['attribs']['id'])) {
            $info['attribs']['id'] = $info['name'];
        }
        
        if (strtolower($info['type']) == 'hidden') {
            // hidden elements are a special case
            $this->_hidden[] = $info;
        } else {
            // non-hidden element
            $this->_stack[] = array('element', $info);
        }
        
        return $this;
    }
    
    /**
     *
     * Automatically adds multiple pieces to the form.
     * 
     * @param Solar_Form|array If a Solar_Form object, adds
     * attributes, elements and feedback from the object properties. 
     * If an array, treats it as a a collection of element info
     * arrays and adds them.
     * 
     * @return Solar_View_Helper_Form
     * 
     */
    public function auto($spec)
    {
        if ($spec instanceof Solar_Form) {
            
            // add from a Solar_Form object.
            // set attributes
            foreach ((array) $spec->attribs as $key => $val) {
                $this->setAttrib($key, $val);
            }
            
            // add form-level feedback
            $this->addFeedback($spec->feedback);
            
            // add elements
            foreach ((array) $spec->elements as $info) {
                $this->addElement($info);
            }
            
        } elseif (is_array($spec)) {
            
            // add from an array of elements.
            foreach ($spec as $info) {
                $this->addElement($info);
            }
        }
        
        // done
        return $this;
    }
    
    public function beginGroup($label = null)
    {
        $this->_stack[] = array('group', array(true, $label));
        return $this;
    }
    
    public function endGroup()
    {
        $this->_stack[] = array('group', array(false, null));
        return $this;
    }
    
    /**
     *
     * Builds and returns the form output.
     * 
     */
    public function fetch()
    {
        // the form tag
        $form = array();
        $form[] = '<form' . $this->_view->attribs($this->_attribs) . '>';
        
        // the form-level feedback list
        $form[] = $this->listFeedback($this->_feedback);
        
        // the hidden elements
        foreach ($this->_hidden as $info) {
            $form[] = $this->_view->formHidden($info);
        }
        
        // loop through the stack
        $in_group = false;
        $form[] = '<dl>';
        
        foreach ($this->_stack as $key => $val) {
            
            $type = $val[0];
            $info = $val[1];
            
            if ($type == 'element') {
                
                // global 'disable' for elements
                // (if null then leave to the element to decide)
                if (is_bool($this->_disable)) {
                    $info['disable'] = $this->_disable;
                }
                
                // setup
                $star     = $info['require'] ? '*' : '';
                $label    = $this->_view->escape($info['label']);
                $id       = $this->_view->escape($info['attribs']['id']);
                $helper   = 'form' . ucfirst($info['type']);
                $element  = $this->_view->$helper($info);
                
                if ($in_group) {
                    $feedback .= $this->listFeedback($info['feedback']);
                    $form[] = "\t\t$element";
                } else {
                    $feedback = $this->listFeedback($info['feedback']);
                    $form[] = "\t<dt>$star<label for=\"$id\">$label</label></dt>";
                    $form[] = "\t<dd>$element$feedback</dd>";
                    $form[] = '';
                }
                
            } elseif ($type == 'group') {
            
                $flag = $info[0];
                $label = $info[1];
                if ($flag) {
                    $in_group = true;
                    $form[] = "\t<dt><label>$label</label></dt>";
                    $form[] = "\t<dd>";
                    $feedback = '';
                } else {
                    $in_group = false;
                    $form[] = "$feedback</dd>";
                    $form[] = '';
                }
            }
            /** @todo add split() for </dl><dl> output? */
            
        }
        $form[] = '</dl>';
        
        
        // and add a closing form tag.
        $form[] = '</form>';
        
        // reset for the next pass
        $this->reset();
        
        // done, return the output!
        return implode("\n", $form);
    }
    
    /**
     *
     * Returns a feedback array (form-level or element-level) as an unordered list.
     * 
     * @return Solar_View_Helper_Form
     * 
     */
    public function listFeedback($spec)
    {
        if (! empty($spec)) {
            $list = array();
            $list[] = '<ul>';
            foreach ((array) $spec as $text) {
                $list[] = '<li>'. $this->_view->escape($text) . '</li>';
            }
            $list[] = '</ul>';
            return "\n" . implode("\n", $list) . "\n";
        }
    }
    
    /**
     *
     * Resets the form entirely.
     * 
     * @return Solar_View_Helper_Form
     * 
     */
    public function reset()
    {
        $this->_attribs = array_merge(
            $this->_default_attribs,
            $this->_config['attribs']
        );
        $this->_feedback = array();
        $this->_hidden = array();
        $this->_stack = array();
        
        return $this;
    }
}
?>