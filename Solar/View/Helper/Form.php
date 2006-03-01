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
 * Solar_View_Helper
 */
require_once 'Solar/View/Helper.php';
 
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
 */
class Solar_View_Helper_Form extends Solar_View_Helper {
    
    protected $_config = array(
        'attribs' => array(),
    );
    
    /**
     * 
     * Attributes for the form tag.
     * 
     */
    protected $_attribs = array();
    
    /**
     * 
     * Collection of form-level feedback messages
     * 
     */
    protected $_feedback = array();
    
    /**
     * 
     * Collection of hidden elements.
     * 
     */
    protected $_hidden = array();
    
    /**
     *
     * Stack of elements and layout.
     * 
     */
    protected $_stack = array();
    
    /**
     *
     * Default form tag attributes.
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
     */
    protected $_disable = null;
    
    /**
     *
     * Constructor.
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
     * Allows $this->elementName() internally, and $this->form()->elementType() externally.
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
     * Main method for the Solar_View::__call() magic.
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
     */
    public function setAttrib($key, $val = null)
    {
        $this->_attribs[$key] = $val;
        return $this;
    }
    
    /**
     *
     * Adds to the form-level feedback array.
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
     */
    public function addElement($info)
    {
        $info = array_merge($this->_default_info, $info);
        
        if (empty($info['type'])) {
            throw new Exception('Need a type for the element.');
        }
        
        if (empty($info['name'])) {
            Solar::dump($info);
            throw new Exception('Need a name for the element.');
        }
        
        if (empty($info['attribs']['id'])) {
            $info['attribs']['id'] = $info['name'];
        }
        
        if ($info['type'] == 'hidden') {
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
     * Automatically builds all or part of the form.
     * 
     */
    public function auto($spec)
    {
        if ($spec instanceof Solar_Form) {
            
            // build from a Solar_Form object.
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
            
            // build from an array of elements.
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