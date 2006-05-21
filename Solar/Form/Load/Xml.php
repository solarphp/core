<?php
/**
 * 
 * Class for loading Solar_Form definitions from a SimpleXML file.
 * 
 * @category Solar
 * 
 * @package Solar_Form
 * 
 * @author Matthew Weier O'Phinney <mweierophinney@gmail.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Class for loading Solar_Form definitions from a SimpleXML file.
 *  
 * @category Solar
 * 
 * @package Solar_Form
 * 
 */
class Solar_Form_Load_Xml extends Solar_Base {
    
    /**
     * 
     * User-defined configuration array.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'locale' => 'Solar/Form/Locale/'
    );
    
    /**
     * 
     * Array of element attribute names.
     * 
     * Used by Solar_Form_Load_Xml::fetch() to get element attributes.
     * 
     * @var array
     * 
     */
    protected $_elementAttribs = array(
        'type',
        'value',
        'require',
        'disable',
    );

    /**
     * 
     * Loads a Solar_Form definition from an XML file.
     * 
     * Loads a Solar_Form definition from an XML file using PHP5's
     * SimpleXML functions.
     * 
     * The location of a form definition file is required.
     * 
     * If an error occurs, a Solar error is generated and returned.
     * 
     * @param string $filename Path to form definition file.
     * 
     * @return object|false Solar_Form object, boolean false on error.
     * 
     */
    public function fetch($filename) 
    {
        $args     = func_get_args();
        $filename = array_shift($args);
        $param    = array_shift($args);

        if (! file_exists($filename)) {
            throw $this->_exception('ERR_FILE_NOT_FOUND');
        }

        // load the XML file data
        $xml = simplexml_load_file($filename);
        if (false === $xml) {
            // return an error here
            return $this->_exception(
                'ERR_BAD_XML',
                array(
                    'filename' => $filename
                )
            );
        }
        
        // loop through the XML file data elements
        $elements = array();
        foreach ($xml->element as $element) {
            
            // skip empty element names ...
            if (empty($element['name'])) {
                continue;
            }
            
            // ... otherwise, get element name and initialize array
            $name = (string) $element['name'];
            $elementInfo = array();

            // Get element attributes
            foreach ($this->elementAttribs as $attrib) {
                if (isset($element[$attrib])) {
                    $elementInfo[$attrib] = (string) $element[$attrib];
                }
            }

            // Get element label/description, if present
            if (!empty($element->label)) {
                $elementInfo['label'] = (string) $element->label;
            }
            if (!empty($element->descr)) {
                $elementInfo['descr'] = (string) $element->descr;
            }

            // Get attribs and options
            foreach (array('attribs', 'options') as $opt) {
                if (!empty($element->$opt)) {
                    $info = array();
                    foreach ($element->$opt as $data) {
                        if (empty($data['name'])) continue;
                        $info[(string) $data['name']] = (string) $data;
                    }
                    $elementInfo[$opt] = $info;
                }
            }

            // Get element filters
            if (! empty($element->filters)) {
                $filters = array();
                foreach ($element->filters->filter as $filter) {
                    
                    if (empty($filter['method'])) continue;
                    $method = (string) $filter['method'];

                    $params    = array();
                    $tmpFilter = array($method);

                    // Were any arguments passed?
                    if (!empty($filter->params)) {
                        $params = $this->getParams($filter->params->param);
                    }
                    
                    // Add arguments to filter array
                    foreach ($params as $param) {
                        array_push($tmpFilter, $param);
                    }

                    // Add filter to element
                    $filters[] = $tmpFilter;
                }
                $elementInfo['filter'] = $filters;
            }
            
            // Get element validations
            if (! empty($element->validate)) {
                $validate = array();
                foreach ($element->validate->rule as $rule) {
                    if (empty($rule['method'])) {
                        continue;
                    }
                    
                    $method  = (string) $rule['method'];
                    $message = '';
                    if (! empty($rule->message)) {
                        $message = (string) $rule->message;
                    }
                    
                    $params  = array();
                    $tmpRule = array($method, $message);

                    // Were any arguments passed?
                    if (! empty($rule->args)) {
                        $params = $this->getParams($rule->args->arg);
                    }

                    // Add arguments to validation array
                    foreach ($params as $param) {
                        array_push($tmpRule, $param);
                    }

                    // Add validation to element
                    $validate[] = $tmpRule;
                }

                $elementInfo['valid'] = $validate;
            }
            
            // Add element to formElements array
            $elements[$name] = $elementInfo;
        }

        return array(
            'attribs'  => array(),
            'elements' => $elements
        );
    }
    
    /**
     * 
     * Gets parameters for a filter or validation rule.
     * 
     * @param array $params The array of parameters.
     * 
     * @return array
     * 
     */
    protected function _getParams($params) 
    {
        $final = array();
        foreach ($params as $param) {
            // Get the parameter type
            // 'array' means it's an array; anything other than
            // 'array' will be ignored.
            $argType = 'args';
            if (!empty($param['type'])) {
                $argType = (string) $param['type'];
            }

            if ('array' == $argType) {
                if (empty($param->item)) {
                    $param = (array) $param;
                } else {
                    $items = array();
                    foreach ($param->item as $item) {
                        $value = (string) $item;
                        if (empty($item['name'])) {
                            $items[] = $value;
                        } else {
                            $items[(string) $item['name']] = $value;
                        }
                    }
                    $param = $items;
                }
            } else {
                if (empty($param->item)) {
                    $param = (string) $param;
                } else {
                    $param = (string) $param->item;
                }
            }

            array_push($final, $param);
        }

        return $final;
    }
}
?>