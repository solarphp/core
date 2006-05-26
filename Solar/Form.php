<?php
/**
 * 
 * Form-processing class; also hints the view on how to present the form.
 * 
 * @category Solar
 * 
 * @package Solar_Form
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @author Contributions from Matthew Weier O'Phinney <mweierophinney@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Form-processing class; also hints the view on how to present the form.
 * 
 * @category Solar
 * 
 * @package Solar_Form
 * 
 */
class Solar_Form extends Solar_Base {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are:
     * 
     * : \\attribs\\ : An array of <form> tag attributes; used for hinting
     * the view on how to present the form.  Defaults are 'method="post"',
     * 'action=$_SERVER["REQUEST_URI"]', and 'enctype="multipart/form-data"'.
     * 
     * : \\success\\ : The overall "success" message when validating form
     * input. Default is Solar locale key SUCCESS_FORM.
     * 
     * : \\failure\\ : The overall "failure" message when validating form
     * input. Default is Solar locale key FAILURE_FORM.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'attribs' => array(
            'action'  => null,
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
        ),
        'success' => null,
        'failure' => null,
    );
    
    /**
     * 
     * The validation status of the form.
     * 
     * @var bool Null if validation has not occurred yet, true if
     * valid, false if not valid.
     */
    protected $_status = null;
    
    /**
     * 
     * Attributes for the form tag itself.
     * 
     * The \\$attribs\\ array holds HTML attributes for the
     * form itself (not for individual elements) such as
     * \\action\\, \\method\\, and \\enctype\\.  Note that these
     * are "hints" for the presentation of the form, and may not
     * be honored by the view.
     * 
     * @var array
     * 
     */
    public $attribs = array();
    
    /**
     * 
     * The array of elements in this form.
     * 
     * The \\$elements\\ array contains all elements in the form,
     * including their names, types, values, any feedback messages,
     * validation and filter callbacks, and so on. 
     * 
     * In general, you should not try to set $elements yourself;
     * instead, Solar_Form::setElement() and Solar_Form::setElements().
     * 
     * @var array
     * 
     */
    public $elements = array();
    
    /**
     * 
     * Overall feedback about the state of the form.
     * 
     * The \\$feedback\\ array stores feedback messages for
     * the form itself (not for individual elements). For example,
     * "Saved successfully." or "Please correct the noted errors."
     * Each array element is an additional feedback message.
     * 
     * Note that the $feedback property pertains to the form as a
     * whole, not the individual elements.  This is as opposed to
     * the 'feedback' key in each of the elements, which contains
     * feedback specific to that element.
     * 
     * @var array
     * 
     */
    public $feedback = array();
    
    /**
     * 
     * The array of pre-filters for the form elements.
     * 
     * @var array 
     * 
     */
    protected $_filter = array();
    
    /**
     * 
     * The array of validations for the form elements.
     * 
     * @var array
     * 
     */
    protected $_valid = array();
    
    /**
     * 
     * Array of submitted values.
     * 
     * Populated on the first call to Solar_Form::_populate(), which itself uses
     * Solar::get() or Solar::post(), depending on the value of
     * $this->attribs['method'].
     * 
     * @var array
     * 
     * @todo Do we really need this as a property?
     * 
     */
    protected $_submitted = null;
    
    /**
     * 
     * Default values for each element.
     * 
     * Keys are:
     * 
     * : \\name\\ : (string) The name attribute.
     * 
     * : \\type\\ : (string) The input or type attribute ('text', 'select', etc).
     * 
     * : \\label\\ : (string) A short label for the element.
     * 
     * : \\value\\ : (string) The default or selected value(s) for the element.
     * 
     * : \\descr\\ : (string) A longer description of the element, e.g. a tooltip
     * or help text.
     * 
     * : \\require\\ : (bool) Whether or not the element is required.
     * 
     * : \\disable\\ : (bool) If disabled, the element is read-only (but is still
     * submitted with other elements).
     * 
     * : \\options\\ : (array) The list of allowed values as options for this element
     * as an associative array in the form (value => label).
     * 
     * : \\attribs\\ : (array) Additional XHTML attributes for the element in the
     * form (attribute => value).
     * 
     * : \\feedback\\ : (array) An array of feedback messages for this element,
     * generally based on validation of previous user input.
     * 
     * @var array
     * 
     */
    protected $_default = array(
        'name'     => null,
        'type'     => null,
        'label'    => null,
        'descr'    => null,
        'value'    => null,
        'require'  => false,
        'disable'  => false,
        'options'  => array(),
        'attribs'  => array(),
        'feedback' => array(),
    );
    
    /**
     * 
     * A Solar_Filter object for internal filtering needs.
     * 
     * @var Solar_Filter
     * 
     */
    protected $_obj_filter;
    
    /**
     * 
     * A Solar_Valid object for internal validation needs.
     * 
     * @var Solar_Valid
     * 
     */
    protected $_obj_vaild;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // programmatic defaults
        $this->_config['attribs']['action'] = Solar::server('REQUEST_URI');
        $this->_config['success'] = $this->locale('SUCCESS_FORM');
        $this->_config['failure'] = $this->locale('FAILURE_FORM');
        
        // "real" contruction
        parent::__construct($config);
        
        // retain setups, create validator/filter objects
        $this->attribs = $this->_config['attribs'];
        $this->_obj_filter = Solar::factory('Solar_Filter');
        $this->_obj_valid = Solar::factory('Solar_Valid');
    }
    
    // -----------------------------------------------------------------
    // 
    // Element-management methods
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Sets one element in the form.  Appends if element does not exist.
     * 
     * @param string $name The element name to set or add; overrides
     * $info['name'].
     * 
     * @param array $info Element information using the same keys as
     * in Solar_Form::$_default.
     * 
     * @param string $array Rename the element as a key in this array.
     * 
     * @return void
     * 
     */
    public function setElement($name, $info, $array = null)
    {
        // prepare the name as an array key?
        $name = $this->_prepareName($name, $array);
        
        // prepare the element info
        $info = array_merge($this->_default, $info);
        
        // forcibly cast each of the keys into the elements array
        $this->elements[$name] = array (
            'name'     =>          $name,
            'type'     => (string) $info['type'],
            'label'    => (string) $info['label'],
            'value'    =>          $info['value'], // mixed
            'descr'    => (string) $info['descr'],
            'require'  => (bool)   $info['require'],
            'disable'  => (bool)   $info['disable'],
            'options'  => (array)  $info['options'],
            'attribs'  => (array)  $info['attribs'],
            'feedback' => (array)  $info['feedback'],
        );
        
        // add filters
        if (array_key_exists('filter', $info)) {
            foreach ( (array) $info['filter'] as $args) {
                $this->_filter[$name][] = $args;
            }
        }
        
        // add validations
        if (array_key_exists('valid', $info)) {
        
            foreach ( (array) $info['valid'] as $args) {
            
                // make sure $args is an array
                settype($args, 'array');
                
                // shift the name onto the top of the args
                array_unshift($args, $name);
                
                // add the validation to the element
                call_user_func_array(
                    array($this, 'addValid'),
                    $args
                );
                
            }
        }
    }
    
    /**
     * 
     * Sets multiple elements in the form.  Appends if they do not exist.
     * 
     * @param array $list Element information as array(name => info), where
     * each info value is an array like Solar_Form::$_default.
     * 
     * @param string $array Rename each element as a key in this array.
     * 
     * @return void
     * 
     */
    public function setElements($list, $array = null)
    {
        foreach ($list as $name => $info) {
            $this->setElement($name, $info, $array);
        }
    }
    
    /**
     * 
     * Reorders the existing elements.
     * 
     * @param array $list The order in which elements should be placed; each
     * value in the array is an element name.
     * 
     * @return void
     * 
     */
    public function orderElements($list)
    {
        // the set of elements as they are now
        $old = $this->elements;
        // reset the elements to blank
        $this->elements = array();
        // put the elements in the requested order
        foreach ((array) $list as $name) {
            if (isset($old[$name])) {
                $this->elements[$name] = $old[$name];
            }
        }
        // retain all remaining old elements
        foreach ($old as $name => $info) {
            $this->elements[$name] = $info;
        }
        // done!
    }
    
    /**
     * 
     * Adds a Solar_Filter method callback for an element.
     * 
     * All pre-filters are applied via 
     * Solar_Filter::multiple() and should conform to the 
     * specifications for that method.
     * 
     * All parameters after $method are treated as added parameters
     * for the Solar_Filter method call.
     * 
     * @param string $name The element name.
     * 
     * @param string $method Solar_Filter method or PHP function to use
     * for filtering.
     * 
     * @return void
     * 
     */
    public function addFilter($name, $method) 
    {
        // Get the arguments, drop the element name
        $args = func_get_args();
        array_shift($args);

        $this->_filter[$name][] = $args;
    }
    
    /**
     * 
     * Adds a Solar_Valid method callback as a validation for an element.
     * 
     * @param string $name The element name.
     * 
     * @param string $method The Solar_Valid callback method.
     * 
     * @param string $message The feedback message to use if validation fails.
     * 
     * @return void
     * 
     */
    public function addValid($name, $method, $message = null)
    {
        // get the arguments, drop the element name
        $args = func_get_args();
        $name = array_shift($args);
        
        // add a default validation message (args[0] is the method,
        // args[1] is the message)
        if (empty($args[1]) || trim($args[1]) == '') {
            
            // see if we have an method-specific validation message
            $key = 'VALID_' . strtoupper($method);
            $args[1] = Solar::locale('Solar', $key);
            
            // if the message is the same as the key,
            // there was no method-specific validation
            // message.  revert to the generic default.
            if ($key == $args[1]) {
                $args[1] = Solar::locale('Solar', 'ERR_INVALID');
            }
        }
        
        // add to the validation array
        $this->_valid[$name][] = $args;
    }
    
    /**
     * 
     * Adds multiple feedback messages to elements.
     * 
     * @param array $list An associative array where the key is an element
     * name and the value is a string or sequential array of feedback messages.
     * 
     * @param string $array Rename each element as a key in this array.
     * 
     * @return void
     * 
     */
    public function addFeedback($list, $array = null)
    {
        foreach ($list as $name => $feedback) {
            $name = $this->_prepareName($name, $array);
            settype($feedback, 'array');
            foreach ($feedback as $text) {
                $this->elements[$name]['feedback'][] = $text;
            }
        }
    }
    
    
    // -----------------------------------------------------------------
    // 
    // Value-management methods
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Populates form elements with specified values.
     * 
     * @param array $submit The source data array for populating form
     * values as array(name => value); if null, will populate from $_POST
     * or $_GET as determined from the Solar_Form::$attribs['method'] value.
     * 
     * @return void
     * 
     */
    public function populate($submit = null)
    {
        $this->_submitted = array();
        $this->_status = null;
        
        // import the submitted values
        if (is_array($submit)) {
            // from an array
            $this->_submitted = $submit;
        } elseif (is_object($submit)) {
            // from an object
            $this->_submitted = (array) $submit;
        } else {
            // from $_GET or $_POST, per the form method.
            $method = strtolower($this->attribs['method']);
            if ($method == 'get' || $method == 'post') {
                $this->_submitted = Solar::$method();
            }
        }
        
        // populate the submitted values into the
        // elements themsevles.
        $this->_populate($this->_submitted);
    }
    
    /**
     * 
     * Performs filtering and validation on each form element.
     * 
     * Updates the feedback keys for each element that fails validation.
     * Values are either pulled from the submitted form or from the array
     * passed in $submit.
     * 
     * @param array $submit The source data array for populating form
     * values as array(name => info); if null, will populate from $_POST
     * or $_GET as determined from the 'method' attribute.
     * 
     * @return bool True if all elements are valid, false if not.
     * 
     */
    public function validate($submit = null)
    {
        // Populate the form values.
        if (empty($this->_submitted)) {
            $this->populate($submit);
        }

        // Loop through each element to filter
        foreach ($this->_filter as $name => $filters) {
            $value = $this->elements[$name]['value'];
            $this->elements[$name]['value'] = $this->_obj_filter->multiple(
                $value, $filters
            );
        }

        $validated = true;
        
        // loop through each element to be validated
        foreach ($this->_valid as $name => $list) {
            
            // loop through each validation for the element
            foreach ($list as $args) {
                
                // the name of the Solar_Valid method
                $method = array_shift($args);
                
                // the text of the error message
                $feedback = array_shift($args);
                
                // config is now the remaining arguments,
                // put the value on top of it.
                array_unshift($args, $this->elements[$name]['value']);
                
                // call the appropriate Solar_Valid method.
                $result = call_user_func_array(
                    array($this->_obj_valid, $method),
                    $args
                );
                
                // was it valid?
                if (! $result) {
                    // no, add the feedback message
                    $validated = false;
                    $this->elements[$name]['feedback'][] = $feedback;
                }
                
            } // inner loop of validations
            
        } // outer loop of elements
        
        if ($validated) {
            $this->feedback = array($this->_config['success']);
        } else {
            $this->feedback = array($this->_config['failure']);
        }
        
        $this->_status = $validated;
        return $validated;
    }
    
    /**
     * 
     * Returns the form element values as an array.
     * 
     * @param string $key Return only values that are part of
     * this array key.  If null, returns all values in the
     * form.
     * 
     * @return array An associative array of element values.
     * 
     */
    public function values($key = null)
    {
        $values = array();
        foreach ($this->elements as $name => $elem) {
            $this->_values($name, $elem['value'], $values);
        }
        
        if (! $key) {
            return $values;
        }
        
        if (! empty($values[$key])) {
            return $values[$key];
        }
    }
    
    
    // -----------------------------------------------------------------
    // 
    // General-purpose methods
    // 
    // -----------------------------------------------------------------
    
    /**
     * 
     * Resets the form object to its originally-configured state.
     * 
     * This clears out all elements, filters, validations, and feedback,
     * as well as all submitted values.  Use this method to "start over
     * again" using the same form object.
     * 
     * @return void
     * 
     */
    public function reset()
    {
        $this->attribs    = $this->_config['attribs'];
        $this->elements   = array();
        $this->feedback   = null;
        $this->_filter    = array();
        $this->_valid     = array();
        $this->_submitted = null;
    }
    
    /**
     * 
     * Forcibly sets the form status.
     * 
     * @param bool $flag True if you want to say the form is valid,
     * false if you want to say it is not valid.
     * 
     * @return void
     * 
     */
    public function setStatus($flag)
    {
        if ($flag === null) {
            $this->_status = null;
        } else {
            $this->_status = (bool) $flag;
        }
    }
    
    /**
     * 
     * Gets the current form status.
     * 
     * @return bool
     * 
     */
    public function getStatus()
    {
        return $this->_status;
    }
    
    /**
     * 
     * Loads form attributes and elements from an external source.
     * 
     * You can pass an arbitrary number of parameters to this method;
     * all params after the first will be passed as arguments to the
     * fetch() method of the loader class.
     * 
     * The loader class itself must have at least one method, fetch(),
     * that returns an associative array with keys 'attribs' and 
     * 'elements' which contain, respectively, values for $this->attribs
     * and $this->setElements().
     * 
     * Example use:
     * 
     * <code>
     * $form = Solar::factory('Solar_Form');
     * $form->load('Solar_Form_Load_Xml', '/path/to/form.xml');
     * </code>
     * 
     * @param string|object $obj If a string, it is treated as a class
     * name to instantiate with Solar::factory(); if an object, it is
     * used as-is.  Either way, the fetch() method of the object will
     * be called to populate this form (via $this->attribs property and
     * the $this->setElements() method).
     * 
     * @return void
     * 
     */
    public function load($obj)
    {
        // if the first param is a string class name
        // try to instantiate it.
        if (is_string($obj)) {
            $obj = Solar::factory($obj);
        }
        
        // if we *still* don't have an object, or if there's no
        // fetch() method, there's a problem.
        if (! is_object($obj) ||
            ! is_callable(array($obj, 'fetch'))) {
            throw $this->_exception(
                'ERR_METHOD_NOT_CALLABLE',
                array('method' => 'fetch')
            );
        }
        
        // get any additional arguments to pass to the fetch
        // method (after dropping the first param) ...
        $args = func_get_args();
        array_shift($args);
        
        // ... and call the fetch method.
        $info = call_user_func_array(
            array($obj, 'fetch'),
            $args
        );
        
        // we don't call reset() because there are
        // sure to be cases when you need to load()
        // more than once to get a full form.
        // 
        // merge the loaded attribs onto the current ones.
        $this->attribs = array_merge(
            $this->attribs,
            $info['attribs']
        );
        
        // add elements, overwriting existing ones (no way
        // around this, I'm afraid).
        $this->setElements($info['elements']);
    }
    
    // -----------------------------------------------------------------
    //
    // Support methods
    //
    // -----------------------------------------------------------------
    
    /**
     * 
     * Prepares a name as an array key, if needed.
     * 
     * @param string $name The element name.
     * 
     * @param string $array The array name, if any, into which we place
     * the element.
     * 
     * @return string The prepared element name.
     * 
     */
    protected function _prepareName($name, $array = null)
    {
        if ($array) {
            $pos = strpos($name, '[');
            if ($pos === false) {
                // name is not itself an array.
                // e.g., 'field' becomes 'array[field]'
                $name = $array . "[$name]";
            } else {
                // the name already has array keys, e.g.
                // 'field[0]'. make the name just another key
                // in the array, e.g. 'array[field][0]'.
                $name = $array . '[' .
                    substr($name, 0, $pos) . ']' .
                    substr($name, $pos);
            }
        }
        return $name;
    }
    
    /**
     * 
     * Recursive method to map the submitted values into elements.
     * 
     * @param array|string $src The source data populating form
     * values.  If an array, will recursively descend into the array;
     * if a scalar, will map the value into a form element.
     * 
     * @param string $elem The name of the current element mapped from
     * the array of submitted values.  E.g., $src['foo']['bar']['baz']
     * maps to "foo[bar][baz]".
     * 
     * @return void
     * 
     */
    protected function _populate($src, $elem = null)
    {
        // are we working with an array?
        if (is_array($src)) {
            // yes, descend through each of the sub-elements.
            foreach ($src as $key => $val) {
                $sub = empty($elem) ? $key : $elem . "[$key]";
                $this->_populate($val, $sub);
            }
        } else {
            // populate an element value, but only if it exists.
            if (isset($this->elements[$elem])) {
                $this->elements[$elem]['value'] = $src;
            }
        }
    }
    
    /**
     * 
     * Recursively pulls values from elements into an associative array.
     * 
     * @param string $key The current array key for the values array.  If
     * this has square brackets in it, that's a sign we need to keep creating
     * sub-elements for the values array.
     * 
     * @param mixed $val The element value to put into the values array, once
     * we stop creating sub-elements based on the element name.
     * 
     * @param array &$values The values array into which we will place the
     * element value.  Note that it becomes a reference to sub-elements as
     * the recursive function creates new sub-elements based on the form
     * element name.
     * 
     * @return void
     * 
     */
    protected function _values($key, $val, &$values)
    {
        if (strpos($key, '[') === false) {
        
            // no '[' in the key, so we're at the end
            // of any recursive descent; capture the value.
            if (empty($key)) {
                // handles elements named as auto-append arrays '[]'
                $values[] = $val;
            } else {
                $values[$key] = $val;
            }
            return;
            
        } else {
        
            // recursively parse the element name ($key) to create an
            // array-key for its value.
            // 
            // $key is something like "foo[bar][baz]".
            // 
            // 0123456789012
            // foo[bar][baz]
            // 
            // find the first '['.
            $pos = strpos($key, '[');
            
            // the part before the '[' is the new value key
            $new = substr($key, 0, $pos);
            
            // the part after the '[' still needs to be processed
            $key = substr($key, $pos+1);
            
            // create $values['foo'] if it does not exist.
            if (! isset($values[$new])) {
                $values[$new] = null;
            }
            
            // now $key is something like "bar][baz]".
            // 
            // 012345678
            // bar][baz]
            // 
            // remove the first remaining ']'.  this should leave us
            // with 'bar[baz]'.
            $pos = strpos($key, ']');
            $key = substr_replace($key, '', $pos, 1);
            
            
            // continue to descend,
            // but relative to the new value array.
            $this->_values($key, $val, $values[$new]);
        }
    }
}
?>