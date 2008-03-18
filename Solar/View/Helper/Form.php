<?php
/**
 * 
 * Helper for building CSS-based forms.
 * 
 * This is a fluent class; all method calls except fetch() return
 * $this, which means you can chain method calls for easier readability.
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
class Solar_View_Helper_Form extends Solar_View_Helper {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * @var array
     */
    protected $_Solar_View_Helper_Form = array(
        'attribs' => array(),
        'descr_elem' => 'dd',
        'descr_tag' => 'div',
        'descr_class' => 'descr',
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
     * Tracks element IDs so we can have unique IDs for each element.
     * 
     * @var array
     * 
     */
    protected $_id_count = array();
    
    /**
     * 
     * CSS classes to use for element and feedback types.
     * 
     * Array format is type => css-class.
     * 
     * @var array
     * 
     */
    protected $_css_class = array(
        'button'   => 'input-button',
        'checkbox' => 'input-checkbox',
        'file'     => 'input-file',
        'hidden'   => 'input-hidden',
        'options'  => 'input-option',
        'password' => 'input-password',
        'radio'    => 'input-radio',
        'reset'    => 'input-reset',
        'select'   => 'input-select',
        'submit'   => 'input-submit',
        'text'     => 'input-text',
        'textarea' => 'input-textarea',
        'failure'  => 'failure',
        'success'  => 'success',
        'require'  => 'require',
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
        'descr'    => '',
        'status'   => null,
        'attribs'  => array(),
        'options'  => array(),
        'disable'  => false,
        'require'  => false,
        'invalid' => array(),
    );
    
    /**
     * 
     * Details about the request environment.
     * 
     * @var Solar_Request
     * 
     */
    protected $_request;
    
    /**
     * 
     * When the raw description XHTML is rendered, do so within this type of
     * tag block (default is 'div' for a `<div>...</div>` tag block.)
     * 
     * @var string
     * 
     */
    protected $_descr_tag = 'div';
    
    /**
     * 
     * Use this class attribute when rendering the raw descripton XHTML.
     * Default is 'descr'; e.g., `<div class="descr">...</div>`.
     * 
     * @var string
     * 
     */
    protected $_descr_class = 'descr';
    
    /**
     * When rendering the raw description XHTML, do so within this structural
     * element; default is 'dd' for the value portion (as vs 'dl' for the
     * label portion).
     * 
     * @var string
     * 
     */
    protected $_descr_elem = 'dd';
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // "real" construction
        parent::__construct($config);
        
        // get the current request environment
        $this->_request = Solar_Registry::get('request');
        
        // make sure we have a default action
        $this->_default_attribs['action'] = $this->_request->server('REQUEST_URI');
        
        // reset the form propertes
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
        return $this;
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
            throw $this->_exception('ERR_NO_ELEMENT_TYPE', $info);
        }
        
        if (empty($info['name'])) {
            throw $this->_exception('ERR_NO_ELEMENT_NAME', $info);
        }
        
        // auto-set ID?
        if (empty($info['attribs']['id'])) {
            // convert name[key][subkey] to name-key-subkey
            $info['attribs']['id'] = str_replace(
                    array('[', ']'),
                    array('-', ''),
                    $info['name']
            );
        }
        
        // is this id already in use?
        $id = $info['attribs']['id'];
        if (empty($this->_id_count[$id])) {
            // not used yet, start tracking it
            $this->_id_count[$id] = 1;
        } else {
            // already in use, increment the count.
            // for example, 'this-id' becomes 'this-id-1',
            // next one is 'this-id-2', etc.
            $id .= "-" . $this->_id_count[$id] ++;
            $info['attribs']['id'] = $id;
        }
        
        // auto-set CSS classes for the element?
        if (empty($info['attribs']['class'])) {
            
            // get a CSS class for the element type
            if (! empty($this->_css_class[$info['type']])) {
                $info['attribs']['class'] = $this->_css_class[$info['type']];
            } else {
                $info['attribs']['class'] = '';
            }
            
            // also use the element ID for further overrides
            $info['attribs']['class'] .= ' ' . $info['attribs']['id'];
            
            // passed validation?
            if ($info['status'] === true) {
                $info['attribs']['class'] .= ' ' . $this->_css_class['success'];
            }
            
            // failed validation?
            if ($info['status'] === false) {
                $info['attribs']['class'] .= ' ' . $this->_css_class['failure'];
            }
            
            // required?
            if ($info['require']) {
                $info['attribs']['class'] .= ' ' . $this->_css_class['require'];
            }
        }
        
        // place in the stack, or as hidden?
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
     * Adds a submit button named 'process' to the form, using a translated
     * locale key stub as the submit value.
     * 
     * @param string $key The locale key stub.  E.g., $key is 'save', the
     * submit-button value is the locale translated 'PROCESS_SAVE' string.
     * 
     * @param array $info Additional element info.
     * 
     * @return Solar_View_Helper_Form
     * 
     */
    public function addProcess($key, $info = null)
    {
        $key = 'PROCESS_' . strtoupper($key);
        
        $base = array(
            'type'  => 'submit',
            'name'  => 'process',
            'value' => $this->_view->getTextRaw($key),
        );
        
        $info = array_merge((array) $info, $base);
        
        return $this->addElement($info);
    }
    
    /**
     * 
     * Adds a group of process buttons with an optional label.
     * 
     * @param array $list An array of process button names. Normally you would
     * pass a sequential array ('save', 'delete', 'cancel').  If you like, you
     * can pass the process name as the key, with an associative array value
     * of element info for that particular submit button.
     * 
     * @param string $label The label for the group.
     * 
     * @return Solar_View_Helper_Form
     * 
     */
    public function addProcessGroup($list, $label = null)
    {
        $this->beginGroup($label);
        
        foreach ((array) $list as $key => $val) {
            if (is_array($val)) {
                // $key stays the same
                $info = $val;
            } else {
                // sequential array; the value is the process key.
                $key = $val;
                // no info
                $info = array();
            }
            
            // add the process within the group
            $this->addProcess($key, $info);
        }
        
        $this->endGroup();
        
        return $this;
    }
    
    /**
     * 
     * Sets the form validation status.
     * 
     * @param bool $flag True if you want to say the form is valid,
     * false if you want to say it is not valid, null if you want to 
     * say that validation has not been attempted.
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
     * Gets the form validation status.
     * 
     * @return bool True if the form is currently valid, false if not,
     * null if validation has not been attempted.
     * 
     */
    public function getStatus()
    {
        return $this->_status;
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
     * Begins a <fieldset> block with a legend/caption.
     * 
     * @param string $legend The legend or caption for the fieldset.
     * 
     * @return Solar_View_Helper_Form
     * 
     */
    public function beginFieldset($legend)
    {
        $this->_stack[] = array('fieldset', array(true, $legend));
        return $this;
    }
    
    /**
     * 
     * Ends a <fieldset> block.
     * 
     * @return Solar_View_Helper_Form
     * 
     */
    public function endFieldset()
    {
        $this->_stack[] = array('fieldset', array(false, null));
        return $this;
    }
    
    /**
     * 
     * Builds and returns the form output.
     * 
     * @param bool $with_form_tag If true (the default) outputs the form with
     * <form>...</form> tags.  If false, it does not.
     * 
     * @return string
     * 
     * @todo Refactor this down into sub-methods.
     * 
     */
    public function fetch($with_form_tag = true)
    {
        // stack of output elements
        $form = array();
        
        // the form tag itself?
        if ($with_form_tag) {
            $form[] = '<form' . $this->_view->attribs($this->_attribs) . '>';
        }
        
        // what status class should we use?
        if ($this->_status === true) {
            $class = $this->_css_class['success'];
        } elseif ($this->_status === false) {
            $class = $this->_css_class['failure'];
        } else {
            $class = null;
        }
        
        // add form feedback with proper status class
        $form[] = $this->listFeedback($this->_feedback, $class);
        
        // the hidden elements
        if ($this->_hidden) {
            // wrap in a hidden fieldset for XHTML-Strict compliance
            $form[] = '    <fieldset style="display: none;">';
            foreach ($this->_hidden as $info) {
                $form[] = '        ' . $this->_view->formHidden($info);
            }
            $form[] = '    </fieldset>';
            $form[] = '    ';
        }
        
        // loop through the stack
        $in_dl       = false;
        $in_fieldset = false;
        $in_group    = false;
        
        foreach ($this->_stack as $key => $val) {
            
            $type = $val[0];
            $info = $val[1];
            
            if ($type == 'element') {
                
                // be sure we're in a <dl> block
                if (! $in_dl) {
                    $form[] = '        <dl>';
                    $in_dl = true;
                }
                
                // setup
                $label    = $this->_view->getText($info['label']);
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
                
                // SPECIAL CASE:
                // checkboxes that are not in groups don't get an "extra" label.
                if (strtolower($info['type']) == 'checkbox' && ! $in_group) {
                    $info['label'] = null;
                }
                
                // get the element output
                $element = $helper->$method($info);
                
                // get the element description
                $dt_descr = '';
                $dd_descr = '';
                
                // only build a description if it's non-empty, and isn't a
                // DESCR_* "empty" locale value.
                if ($info['descr'] && substr($info['descr'], 0, 6) != 'DESCR_') {
                    
                    // build the base description.
                    // open the tag ...
                    $descr = "<" . $this->_view->escape($this->_descr_tag);
                    
                    // ... add a CSS class ...
                    if ($this->_descr_class) {
                        $descr .= ' class="'
                               . $this->_view->escape($this->_descr_class)
                               . '"';
                    }
                    
                    // ... add the raw descr XHTML, and close the tag.
                    $descr .= '>' . $info['descr']
                           . '</' . $this->_view->escape($this->_descr_tag) . '>';
                    
                    
                    // build both the <dt> and <dd> forms so we can use
                    // them in the right place.
                    if ($this->_descr_elem == 'dt') {
                        // using <dt>
                        $dt_descr = $descr;
                    } else {
                        // using <dd>
                        $dd_descr = $descr;
                    }
                }
                       
                // add the element and its feedback;
                // handle differently if we're in a group.
                if ($in_group) {
                    
                    // append to the group feedback
                    $feedback .= $this->listFeedback($info['invalid']);
                    
                    // add the element itself
                    $form[] = "                $element";
                    
                } else {
                    
                    // is the element required?
                    if ($info['require']) {
                        $require = ' class="' . $this->_css_class['require'] . '"';
                    } else {
                        $require = '';
                    }
                    
                    // get the feedback list
                    $feedback = $this->listFeedback($info['invalid']);
                    
                    // add the form element with all parts in place
                    $form[] = "            <dt$require><label$require for=\"$id\">$label</label>$dt_descr</dt>";
                    $form[] = "            <dd$require>$element$feedback$dd_descr</dd>";
                    $form[] = '';
                    
                }
                
            } elseif ($type == 'group') {
            
                // be sure we're in a <dl> block
                if (! $in_dl) {
                    $form[] = '        <dl>';
                    $in_dl = true;
                }
                
                $flag = $info[0];
                $label = $info[1];
                if ($flag) {
                    $in_group = true;
                    $form[] = "            <dt><label>$label</label></dt>";
                    $form[] = "            <dd>";
                    $feedback = '';
                } else {
                    $in_group = false;
                    if ($feedback) {
                        $form[] = "            $feedback";
                    }
                    $form[] = "            </dd>";
                    $form[] = '';
                }
                
            } elseif ($type == 'fieldset') {
                
                $flag = $info[0];
                $legend = $info[1];
                if ($flag) {
                    $form[] = "    <fieldset><legend>$legend</legend>";
                    $form[] = "        <dl>";
                    $in_fieldset = true;
                    $in_dl = true;
                } else {
                    $form[] = "        </dl>";
                    $form[] = "    </fieldset>";
                    $form[] = '';
                    $in_dl = false;
                    $in_fieldset = false;
                }
            }
        }
        
        if ($in_dl) {
            $form[] = '        </dl>';
        }
        
        if ($in_fieldset) {
            $form[] = '    </fieldset>';
        }
        
        // add a closing form tag?
        if ($with_form_tag) {
            $form[] = '</form>';
        }
        
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
                $list[] = '    <li>'. $this->_view->escape($text) . '</li>';
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
        // attributed for the <form> tag
        $this->_attribs = array_merge(
            $this->_default_attribs,
            $this->_config['attribs']
        );
        
        // form-element descriptions
        $this->_descr_elem = $this->_config['descr_elem'];
        $this->_descr_tag = $this->_config['descr_tag'];
        $this->_descr_class = $this->_config['descr_class'];
        
        // make sure we force the description to be in either the <dt>
        // element or the <dd> portion
        if ($this->_descr_elem != 'dt') {
            $this->_descr_elem = 'dd';
        }
        
        // everything else
        $this->_feedback = array();
        $this->_hidden = array();
        $this->_stack = array();
        $this->_status = null;
        $this->_id_count = array();
        
        return $this;
    }
}
