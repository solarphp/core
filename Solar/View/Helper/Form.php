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
 * @todo Add beginFieldset()/endFieldset()
 * 
 * @todo Rename to FormLayout? FormRender?
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
     * Form CSS class to use for failure/success statuses.
     * 
     * @var array
     * 
     */
    protected $_class = array(
        0 => 'failure',
        1 => 'success',
    );
    
    /**
     * 
     * The current failure/success status.
     * 
     * @var bool
     * 
     */
    protected $_status = null;
    
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
     * Sets the form status.
     * 
     * @param bool $flag True if you want to say the form is valid,
     * false if you want to say it is not valid.
     * 
     * @return Solar_View_Helper_Form
     * 
     */
    public function setStatus($flag)
    {
        if ($flag === null) {
            $this->_status = null;
        } else {
            $this->_status = (bool) $flag;
        }
        return $this;
    }
    
    /**
     * 
     * Automatically adds multiple pieces to the form.
     * 
     * @param Solar_Form|array $spec If a Solar_Form object, adds
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
            // set the form status.
            $this->setStatus($spec->getStatus());
            
            // set the form attributes
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
    
    /**
     * 
     * Begins a group of form elements under a single label.
     * 
     * @param string $label The label text.
     * 
     * @return Solar_View_Helper_Form
     * 
     */
    public function beginGroup($label = null)
    {
        $this->_stack[] = array('group', array(true, $label));
        return $this;
    }
    
    /**
     * 
     * Ends a group of form elements.
     * 
     * @return Solar_View_Helper_Form
     * 
     */
    public function endGroup()
    {
        $this->_stack[] = array('group', array(false, null));
        return $this;
    }
    
    /**
     * 
     * Builds and returns the form output.
     * 
     * @return string
     * 
     */
    public function fetch()
    {
        // the form tag
        $form = array();
        $form[] = '<form' . $this->_view->attribs($this->_attribs) . '>';
        
        // the form-level feedback list, with the proper status
        // class.
        if (is_bool($this->_status)) {
            $class = $this->_class[(int) $this->_status];
        } else {
            $class = null;
        }
        $form[] = $this->listFeedback($this->_feedback, $class);
        
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
                $method   = 'form' . ucfirst($info['type']);
                try {
                    // look for the requested element helper
                    $helper = $this->_view->getHelper($method);
                } catch (Solar_View_Exception $e) {
                    // use 'text' helper as a fallback
                    if ($e->getCode() == 'ERR_HELPER_FILE_NOT_FOUND' ||
                        $e->getCode() == 'ERR_HELPER_CLASS_NOT_FOUND') {
                        // use 'text' helper
                        $method = 'formText';
                        $helper = $this->_view->getHelper($method);
                    }
                }
                
                // get the element output
                $element = $helper->$method($info);
                
                // add the element and its feedback;
                // handle differently if we're in a group.
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
     * @param array $spec An array of messages.
     * 
     * @param string $class The class to use for the <ul> tag.
     * 
     * @return Solar_View_Helper_Form
     * 
     */
    public function listFeedback($spec, $class = null)
    {
        if (! empty($spec)) {
            $list = array();
            if ($class) {
                $list[] = '<ul class="' . $this->_view->escape($class) . '">';
            } else {
                $list[] = '<ul>';
            }
            
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
        $this->_status = null;
        
        return $this;
    }
}
?>