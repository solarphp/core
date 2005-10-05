<?php

/**
* 
* Class for loading form definitions from a file.
* 
* @category Solar
* 
* @package Solar
* 
* @author Matthew Weier O'Phinney <mweierophinney@gmail.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* 
* Class for loading Solar_Form definitions from a SimpleXML file.
* 
* This class loads Solar_Form element definitions from a SimpleXML file.
*
* The XML file format should be something like the following:
* <code>
* <?xml version='1.0' standalone='yes'?>
* <form>
*     <attribs>
*         <attrib name="action">/contact.php</attrib>
*         <attrib name="method">post</attrib>
*         <attrib name="name">contact</attrib>
*     </attribs>
*     <element 
*         name="to"
*         type="select"
*         require="1"
*         disable="0">
*         <label>Contact:</label>
*         <descr>Who do you want to mail?</descr>
*         <filters>
*             <filter method="replace">
*                 <params>
*                     <param>/^(me|someoneelse)$/</param>
*                     <param>$1@solarphp.com</param>
*                 </params>
*             </filter>
*         </filters>
*         <validate>
*             <rule method="inList">
*                 <message>Please select a contact from the dropdown</message>
*                 <args>
*                     <arg>me@solarphp.com</arg>
*                     <arg>someoneelse@solarphp.com</arg>
*                 </args>
*             </rule>
*         </validate>
*     </element>
*     <element
*         name="fromEmail"
*         type="text"
*         require="1"
*         disable="0">
*         <label>Your email address:</label>
*         <filters>
*             <filter method="trim" />
*         </filters>
*         <validate>
*             <rule method="email">
*                 <message>Please provide a valid email address</message>
*             </rule>
*         </validate>
*     </element>
*     <element
*         name="fromName"
*         type="text"
*         require="0"
*         disable="0">
*         <label>Your Name:</label>
*         <filters>
*             <filter method="trim" />
*             <filter method="strip_tags" />
*             <filter method="htmlentities" />
*         </filters>
*     </element>
*     <element
*         name="subject"
*         type="text"
*         require="1"
*         disable="0">
*         <label>Subject:</label>
*         <filters>
*             <filter method="trim" />
*             <filter method="strip_tags" />
*             <filter method="htmlentities" />
*         </filters>
*     </element>
*     <element
*         name="message"
*         type="text"
*         require="1"
*         disable="0">
*         <label>Message:</label>
*     </element>
* </form>
* </code>
*
* Basically, a form consists of attributes and elements. Attributes are related
* to the form as a whole, and spefically the &lt;form&gt; HTML element; elements
* are single elements within the form, and include all information about an
* element, including:
*
* <ul>
*     <li>HTML element name ('name', required)</li>
*     <li>HTML element type ('type', optional)</li>
*     <li>required flag ('require', boolean, optional, defaults to 0)</li>
*     <li>disable flag ('disable', boolean, optional, defaults to 0)</li>
*     <li>HTML label text ('label', text, optional)</li>
*     <li>HTML element attributes ('attribs', array, optional)</li>
*     <li>filters. These are any pre-filters you wish to run on this element
*     before processing, and should be valid {@link Solar_Filter} filters. This
*     is an array, and contains one or more filter elements. These should have:
*     <ul>
*         <li>a 'method' attribute corresponding to a Solar_Filter prefilter</li>
*         <li>If the prefilter requires additional parameters, a params array.
*         Each param in the params array should be a string.</li>
*     </ul>
*     </li>
*     <li>validation rules. These are any Solar_Valid rules you wish to validate
*     the element against -- you can use as many as are needed. These are formed
*     similarly to filters, but a rule takes an extra optional argument:
*     <ul>
*         <li>message: a feedback message to use should the validation fail</li>
*     </ul>
*     </li>
* </ul>
*  
* @category Solar
* 
* @package Solar
* 
*/

class Solar_Form_Load_Xml extends Solar_Base {
	
	protected $config = array(
		'locale' => 'Solar/Form/Locale/'
	);
	
	/**
	* 
	* Array of element attributes; used by {@link fetch()} to get element
	* attributes
	* 
	* @var array
	* 
	* @access protected
	*/
	protected $elementAttribs = array(
		'type',
		'value',
		'require',
		'disable'
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
	* @access public
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
			// Need to return an error here
			return $this->error(
				'ERR_FORM_LOAD_NOFILE',
				array(),
				E_USER_WARNING
			);
		}

		// load the XML file data
		$xml = simplexml_load_file($filename);
		if (false === $xml) {
			// return an error here
			return $this->error(
				'ERR_FORM_LOAD_BADXML',
				array(
					'filename' => $filename
				),
				E_USER_WARNING
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

				$elementInfo['validate'] = $validate;
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
	* Get parameters for a filter or validation rule
	*
	* @access protected
	*
	* @param array
	*
	* @return array
	*/
	
	protected function getParams($params) 
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