<?php

/**
* 
* Plugin to create XHTML forms with CSS and table-based layouts.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id: Savant3_Plugin_form.php,v 1.9 2005/09/13 21:54:39 pmjones Exp $
* 
*/

/**
* 
* Plugin to create XHTML forms with CSS and table-based layouts.
* 
* @package Savant3
* 
* @subpackage Savant3_Plugin_Form
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
*/

class Savant3_Plugin_form extends Savant3_Plugin {
	
	
	/**
	* 
	* The CSS class to use when generating form layout.
	* 
	* This class name will be applied to the following tags:
	* 
	* - div
	* - fieldset
	* - legend
	* - table
	* - tr
	* - th
	* - td
	* - label
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	public $class = '';
	
	
	/**
	* 
	* The default 'float' style for fieldset blocks.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	public $float = '';
	
	
	/**
	* 
	* The default 'clear' style for fieldset blocks.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	public $clear = '';
	
	
	/**
	* 
	* The text used to separate radio buttons in col-type blocks.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	public $radio_col = "<br />\n";
	
	
	/**
	* 
	* The text used to separate radio buttons in row-type blocks.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	public $radio_row = '&nbsp;&nbsp;';
	
	
	/**
	* 
	* The base number of tabs to use when tidying up the generated XHTML.
	* 
	* @access public
	* 
	* @var int
	* 
	*/
	
	public $tab_base = 2;
	
	
	/**
	* 
	* The sprintf() format for feedback in col-type blocks.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	public $feedback_col = '<br /><span style="color: red; font-size: 80%%;">%s</span>';
	
	
	/**
	* 
	* The sprintf() format for feedback in col-type blocks.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	public $feedback_row = '<br /><span style="color: red; font-size: 80%%;">%s</span>';
	
	
	/**
	* 
	* Whether or not the entire form is in "freeze" mode (i.e., read-only).
	* 
	* Term comes from HTML_QuickForm.
	* 
	* @access public
	* 
	* @var bool
	* 
	*/
	
	public $freeze = false;
	
	
	/**
	* 
	* The sign that goes with a label indicating it is a required field.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	public $require_sign = '<span style="color: red;">*&nbsp;</span>';
	
	
	/**
	* 
	* Default values for each element hint.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $_base = array(
		'name'    => null,
		'type'    => null,
		'label'   => null,
		'descr'   => null,
		'value'   => null,
		'require' => false,
		'disable' => false,
		'options' => array(),
		'listsep' => null,
		'attribs' => array(),
		'feedback' => array(),
	);
		

	/**
	* 
	* The kind of fieldset block being generated ('col' or 'row').
	* 
	* @access protected
	* 
	* @var bool
	* 
	*/
	
	protected $_block_type = null;
	
	
	/**
	* 
	* The legend for the fieldset block, if any.
	* 
	* @access protected
	* 
	* @var string
	* 
	*/
	
	protected $_block_label = null;
	
	
	/**
	* 
	* Whether or not the form is generating elements within a fieldset block.
	* 
	* @access protected
	* 
	* @var bool
	* 
	*/
	
	protected $_in_block = false;
	
	
	/**
	* 
	* Whether or not the form is generating elements as a group.
	* 
	* @access protected
	* 
	* @var bool
	* 
	*/
	
	protected $_in_group = false;
	
	
	/**
	* 
	* The number of tabs to use before certain tags when tidying XHTML layout.
	* 
	* @access protected
	* 
	* @var bool
	* 
	*/
	
	protected $_tabs = array(
		'form'                  => 0,
		'/form'                 => 0,
		'div'                   => 1,
		'/div'                  => 1,
		'fieldset'              => 1,
		'/fieldset'             => 1,
		'legend'                => 2,
		'table'                 => 2,
		'/table'                => 2,
		'tr'                    => 3,
		'/tr'                   => 3,
		'th'                    => 4,
		'/th'                   => 4,
		'td'                    => 4,
		'/td'                   => 4,
		'label'                 => 5,
		'input'                 => 5,
		'textarea'              => 5,
		'select'                => 5,
		'/select'               => 5,
		'option'                => 6
	);
	
	
	/**
	* 
	* Central switcher API for the the various public methods.
	* 
	* @access public
	* 
	* @param string $method The public method to call from this class; all
	* additional parameters will be passed to the called method, and all
	* returns from the mehtod will be tidied.
	* 
	* @return string XHTML generated by the public method.
	* 
	*/
	
	public function form($method)
	{
		// only pass calls to public methods (i.e., no leading underscore)
		if (substr($method, 0, 1) != '_') {
			
			// get all arguments and drop the first one (the method name)
			$args = func_get_args();
			array_shift($args);
			
			// call the method, then return the tidied-up XHTML results
			$xhtml = call_user_func_array(array($this, $method), $args);
			return $this->_tidy($xhtml);
		}
	}
	
	
	/**
	* 
	* Convenience caller for the various element types.
	* 
	* Instead of having to construct an element $info array and then use
	* $this->form('element', $info), you can use use this method to call
	* an element type directly.  The order of parameters is:
	* 
	* $this->form(type, name, value, label, attribs, options, listsep)
	* 
	* For example:
	* 
	* <code>
	* // this convenience code ...
	* echo $this->form('text', 'my_field', 'init', 'My Field',
	*   array('size' => 10), null, '');
	* 
	* // is equal to this $info array code:
	* $info = array(
	*   'type' => 'text',
	*   'name' => 'my_field',
	*   'value' => 'init',
	*   'label' => 'My Field',
	*   'attribs' => array('size' => 10),
	*   'options' => null,
	*   'listsep' => '',
	* );
	* echo $this->form('element', $info);
	* </code>
	* 
	* 
	* @access public
	* 
	* @param string $method The public method to call from this class; all
	* additional parameters will be passed to the called method, and all
	* returns from the mehtod will be tidied.
	* 
	* @return string XHTML generated by the public method.
	* 
	*/
	
	public function __call($method, $params)
	{
		// $this->form(type, name, value, label, attribs, options, listsep);
		//                   0     1      2      3        4        5
		
		// make sure there are enough params
		$params = array_pad($params, 6, null);
		
		// element info
		$info = array(
			'type'    => $method,
			'name'    => $params[0],
			'value'   => $params[1],
			'label'   => $params[2],
			'attribs' => $params[3],
			'options' => $params[4],
			'listsep' => $params[5],
			'disable' => $this->freeze,
			'require' => false,
			'feedback' => false,
		);
		
		// co-opt the 'attribs' param to fill in require for layout
		if (! empty($info['attribs']['require'])) {
			$info['require'] = $info['attribs']['require'];
			unset($info['attribs']['require']);
		}
		
		// co-opt the 'attribs' param to fill in feedback for layout
		if (! empty($info['attribs']['feedback'])) {
			$info['feedback'] = $info['attribs']['feedback'];
			unset($info['attribs']['feedback']);
		}
		
		// generate the xhtml
		return $this->element($info);
	}
	
	
	/**
	* 
	* Sets the value of a public property.
	* 
	* @access protected
	* 
	* @param string $key The name of the property to set.
	* 
	* @param mixed $val The new value for the property.
	* 
	* @return void
	* 
	*/
	
	protected function set($key, $val)
	{
		if (substr($key, 0, 1) != '_' && isset($this->$key)) {
			$this->$key = $val;
		}
	}
	
	
	/**
	* 
	* Begins the form.
	* 
	* The form defaults to 'action="$_SERVER['REQUEST_URI']"',
	* 'method="post"', and 'enctype="multipart/form-data", but you can
	* override those, and add any other attributes you like.
	* 
	* @access protected
	* 
	* @param array $attribs Attributes to add to the form tag.
	* 
	* @return string A <form> tag.
	* 
	*/
	
	protected function begin($attribs = null)
	{
		settype($attribs, 'array');
		
		// default action
		if (! isset($attribs['action'])) {
			$attribs['action'] = $_SERVER['REQUEST_URI'];
		}
		
		// default method
		if (! isset($attribs['method'])) {
			$attribs['method'] = 'post';
		}
		
		// default encoding
		if (! isset($attribs['enctype'])) {
			$attribs['enctype'] = 'multipart/form-data';
		}
		
		// start the form
		$xhtml = '<form';
		$xhtml .= $this->Savant->htmlAttribs($attribs) . ">";
		return $xhtml;
	}
	
	
	/**
	* 
	* Ends the form and closes any existing layout.
	* 
	* @access protected
	* 
	* @return string The ending layout XHTML and a </form> tag.
	* 
	*/
	
	protected function end()
	{
		$xhtml = '';
		$xhtml .= $this->group('end');
		$xhtml .= $this->block('end');
		return $xhtml . '</form>';
	}
	
	
	/**
	* 
	* Builds one form element from a hint information array.
	* 
	* @access protected
	* 
	* @param array $info Hint information array.
	* 
	* @param string $arrayName Format the element name as a key in this
	* array name.
	* 
	* @return string 
	* 
	*/
	
	protected function element($info, $arrayName = null)
	{
		// merge in with base element info
		$info = array_merge($this->_base, $info);
		
		// set the list separator based on the block type
		// (only needed for radio elements anyway)
		if ($this->_block_type == 'row') {
			// rows
			$info['listsep'] = $this->radio_row;
		} else {
			// default (columns)
			$info['listsep'] = $this->radio_col;
		}
		
		// set up frozen-ness via the attribs; this tells the element
		// object to freeze the element for read-only.
		if ($info['disable'] || $this->freeze) {
			$info['attribs']['disable'] = true;
		}
		
		// prepare the name as an array key?
		if ($arrayName) {
			$pos = strpos($info['name'], '[');
			if ($pos === false) {
				// name is not itself an array.
				// e.g., 'field' becomes 'array[field]'
				$info['name'] = $arrayName . "[{$info['name']}]";
			} else {
				// the name already has array keys, e.g.
				// 'field[0]'. make the name just another key
				// in the array, e.g. 'array[field][0]'.
				$info['name'] = $arrayName . '[' .
					substr($info['name'], 0, $pos) . ']' .
					substr($info['name'], $pos);
			}
		}
		
		// now process the element
		$method = 'form' . ucfirst($info['type']);
		$xhtml = $this->Savant->$method($info);
		
		// hidden elements don't get layout
		if ($info['type'] != 'hidden') {
			// wrap the element with layout and feedback
			$xhtml = $this->_layout($info['label'], $xhtml, $info['require'],
				$info['feedback']);
		}
		
		// done
		return $xhtml;
	}
	
	
	/**
	* 
	* Builds multiple form elements from a list of hints.
	* 
	* @access protected
	* 
	* @param array $list An array of element hints.
	* 
	* @param string $arrayName For each element, create its name as a key
	* in this array name.
	* 
	* @return string 
	* 
	*/
	
	protected function auto($list, $arrayName = null)
	{
		$xhtml = '';
		foreach ($list as $info) {
			$xhtml .= $this->element($info, $arrayName);
		}
		return $xhtml;
	}
	
	
	/**
	* 
	* Builds XHTML to start, end, or split layout blocks.
	* 
	* @param string $action Whether to 'begin', 'split', or 'end' a block.
	* 
	* @param string $label The fieldset legend.  If an empty string,
	* builds a fieldset with no legend; if null, builds a div (not a
	* fieldset).
	* 
	* @param string $type The layout type to use, 'col' or 'row'.  The
	* 'col' layout uses a left-column for element labels and a
	* right-column for the elements; the 'row' layout shows the elements
	* left-to-right, with the element label over the element, all in a
	* single row.
	* 
	* @param string $float Whether the block should float 'left' or
	* 'right' (set to an empty string if you don't want floating). 
	* Defaults to the value of $this->float.
	* 
	* @param string $float Whether the block should be cleared of 'left'
	* or 'right' floating blocks (set to an empty string if you don't
	* want to clear).  Defaults to the value of $this->clear.
	* 
	* @return string The appropriate XHTML for the block action.
	* 
	* @todo Output an error when calling a non-action.
	* 
	*/
	
	protected function block($action = 'begin', $label = null, $type = 'col', 
		$float = null, $clear = null)
	{
		if (is_null($float)) {
			$float = $this->float;
		}
		
		if (is_null($clear)) {
			$clear = $this->clear;
		}
		
		switch (strtolower($action)) {
		
		case 'begin':
			return $this->_blockBegin($label, $type, $float, $clear);
			break;
		
		case 'split':
			return $this->_blockSplit();
			break;
		
		case 'end':
			return $this->_blockEnd();
			break;
		}
		
		return;
	}
	
	
	/**
	* 
	* Builds the layout for a group of elements; auto-starts a block if needed.
	* 
	* @access protected
	* 
	* @param string $type Whether to 'begin' or 'end' the group.
	* 
	* @param string $label The label for the group.
	* 
	* @return string The element-group layout XHTML.
	* 
	*/
	
	protected function group($type, $label = null, $require = false)
	{
		// the XHTML to return
		$xhtml = '';
		
		// if not in a block, start one
		if (! $this->_in_block) {
			$xhtml .= $this->block();
		}
		
		// group-building action
		switch (strtolower($type)) {
		
		// begin a group...
		case 'begin':
			// ... but only if not already inside one
			if (! $this->_in_group) {
				// build a 'col' group?
				if ($this->_block_type == 'col') {
					
					// start a new row and header cell
					$xhtml .= $this->_tag('tr');
					$xhtml .= $this->_tag('th');
					
					// add a label if specified
					if (! is_null($label)) {
						// start the label tag
						$xhtml .= $this->_tag('label');
						// add a require sign if specified
						if ($require) {
							$xhtml .= $this->require_sign;
						}
						// write the label text, and close.
						$xhtml .= htmlspecialchars($label);
						$xhtml .= '</label>';
					}
					
					// close the header cell and start a data cell
					$xhtml .= '</th>';
					$xhtml .= $this->_tag('td');
				}
			
				// build a 'row' group?
				if ($this->_block_type == 'row') {
					
					// no need for a new row, just start a data cell
					$xhtml .= $this->_tag('td');
					
					// add a label if needed
					if (! is_null($label)) {
						// start the label tag
						$xhtml .= $this->_tag('label');
						// add require sign if needed
						if ($require) {
							$xhtml .= $this->require_sign;
						}
						// write the label text, and done
						$xhtml .= htmlspecialchars($label);
						$xhtml .= '</label><br />';
					}
				}
				
				// we're in a group now
				$this->_in_group = true;
			}
			break;
		
		// end a group ...
		case 'end':
			// ... but only if already inside one
			if ($this->_in_group) {
				// we're out of the group now
				$this->_in_group = false;
				
				// was this a col-type block?
				if ($this->_block_type == 'col') {
					$xhtml .= '</td></tr>';
				}
				
				// was this a row-type block?
				if ($this->_block_type == 'row') {
					$xhtml .= '</td>';
				}
			}
			break;
			
		}
		
		// done!
		return $xhtml;
	}
	
	
	// ---------------------------------------------------------------------
	//
	// Protected support methods
	//
	// ---------------------------------------------------------------------
	
	
	/**
	* 
	* Builds an XHTML opening tag with class and attributes.
	* 
	* @access protected
	* 
	* @param string $type The tag type ('td', 'th', 'div', etc).
	* 
	* @param array $attribs Additional attributes for the tag.
	* 
	* @return string The opening tag XHTML.
	* 
	*/
	
	protected function _tag($type, $attribs = null)
	{
		// force attribs to array
		settype($attribs, 'array');
		
		// set the CSS class attribute if none specified
		if (empty($attribs['class']) && $this->class) {
			$attribs['class'] = $this->class;
		}
		
		// open the tag
		$xhtml = '<' . $type;
		
		// add attributes
		$xhtml .= $this->Savant->htmlAttribs($attribs);
		
		// done!
		return $xhtml . ">";
	}
	
	
	/**
	* 
	* Adds layout to an element; auto-starts a block as needed.
	* 
	* @access protected
	* 
	* @param string $label The label for the element.
	* 
	* @param string $element The XHTML for the element field.
	* 
	* @param bool $require Whether or not the element should have a
	* "required" sign in the label.
	* 
	* @param array|string $feedback One or more feedback strings.
	* 
	* @return string The element layout XHTML.
	* 
	*/
	
	protected function _layout($label, $element, $require = false,
		$feedback = null)
	{
		// the XHTML to return
		$xhtml = '';
		
		// if we're starting an element without having started
		// a block first, forcibly start a default block
		if (! $this->_in_block) {
		
			// is there a label for the element?
			if (is_null($label)) {
				// not in a block, and no label specified. this is most
				// likely a hidden element above the form itself. just
				// return the XHTML as it is, no layout at all.
				return $element;
			} else {
				// start a block and continue
				$xhtml .= $this->block();
			}
		}
		
		// is there any feedback?
		if (! empty($feedback)) {
		
			// force to arrays so we can have multiple feeback strings.
			settype($feedback, 'array');
			
			// pick the format
			if ($this->_in_block && $this->_block_type == 'row') {
				$format = $this->feedback_row;
			} else {
				$format = $this->feedback_col;
			}
			
			// add the feedback
			foreach ($feedback as $text) {
				$element .= sprintf($format, $text);
			}
		}
		
		// are we in a group?
		if (! $this->_in_group) {
			// no, put the element in a group by itself
			$xhtml .= $this->group('begin', $label, $require);
			$xhtml .= $element;
			$xhtml .= $this->group('end');
		} else {
			// yes, just add the element to the current group.
			// elements in groups do not get their own labels,
			// the group has already set the label.
			$xhtml .= $element;
		}
		
		// done!
		return $xhtml;
	}
	
	
	/**
	* 
	* Puts in newlines and tabs to make the source code readable.
	* 
	* @access protected
	* 
	* @param string $xhtml The XHTML to tidy up.
	* 
	* @return string The tidied XHTML.
	* 
	*/
	
	protected function _tidy($xhtml)
	{
		foreach ($this->_tabs as $key => $val) {
			$key = '<' . $key;
			$pad = str_pad('', $val + $this->tab_base, "\t");
			$xhtml = str_replace($key, "\n$pad$key", $xhtml);
		}
		return $xhtml;
	}
	
	
	/**
	* 
	* Generates XHTML to start a fieldset block.
	* 
	* @access protected
	* 
	* @param string $label The fieldset legend.  If an empty string,
	* builds a fieldset with no legend; if null, builds a div (not a
	* fieldset).
	* 
	* @param string $type The layout type to use, 'col' or 'row'.  The
	* 'col' layout uses a left-column for element labels and a
	* right-column for the elements; the 'row' layout shows the elements
	* left-to-right, with the element label over the element, all in a
	* single row.
	* 
	* @param string $float Whether the block should float 'left' or
	* 'right' (set to an empty string if you don't want floating). 
	* Defaults to the value of $this->float.
	* 
	* @param string $float Whether the block should be cleared of 'left'
	* or 'right' floating blocks (set to an empty string if you don't
	* want to clear).  Defaults to the value of $this->clear.
	* 
	* @return string The XHTML to start a block.
	* 
	*/
	
	protected function _blockBegin($label = null, $type = 'col', $float = null,
		$clear = null)
	{
		// the XHTML text to return.
		$xhtml = '';
		
		// are we already in a block? if so, end the current one
		// so we can start a new one.
		if ($this->_in_block) {
			$xhtml .= $this->block('end');
		}
		
		// set the new block type and label
		$this->_in_block = true;
		$this->_block_type = $type;
		$this->_block_label = $label;
		
		// build up the "style" attribute for the new block
		$style = array();
		
		if ($float) {
			$style[] = "float: $float;";
		}
		
		if ($clear) {
			$style[] = "clear: $clear;";
		}
		
		if (! empty($style)) {
			$attribs = array('style' => $style);
		} else {
			$attribs = null;
		}
		
		// build the block opening XHTML itself; use a fieldset when a
		// label is specified, or a div when the label is not specified
		if (is_string($this->_block_label)) {
		
			// has a label, use a fieldset with e style attribute
			$xhtml .=  $this->_tag('fieldset', $attribs);
			
			// add the label as a legend, if it exists
			if (! empty($this->_block_label)) {
				$xhtml .=  $this->_tag('legend');
				$xhtml .= htmlspecialchars($this->_block_label);
				$xhtml .= '</legend>';
			}
			
		} else {
			// no label, use a div with the style attribute
			$xhtml .= $this->_tag('div', $attribs);
		}
		
		// start a table for the block elements
		$xhtml .=  $this->_tag('table');
		
		// if the block is row-based, start a row
		if ($this->_block_type == 'row') {
			$xhtml .=  $this->_tag('tr');
		}
		
		// done!
		return $xhtml;
	}
	
	
	/**
	* 
	* Generates the XHTML to end a block.
	* 
	* @access protected
	* 
	* @return string The XHTML to end a block.
	* 
	*/
	
	protected function _blockEnd()
	{
		// the XHTML to return
		$xhtml = '';
		
		// if not in a block, return right away
		if (! $this->_in_block) {
			return;
		}
		
		// are we in a group?  if so, end it.
		if ($this->_in_group) {
			$xhtml .= $this->group('end');
		}
		
		// end the block layout proper
		if ($this->_block_type == 'row') {
			// previous block was type 'row'
			$xhtml .=  '</tr></table>';
		} else {
			// previous block was type 'col'
			$xhtml .=  '</table>';
		}
		
		// end the fieldset or div tag for the block
		if (is_string($this->_block_label)) {
			// there was a label, so the block used fieldset
			$xhtml .=  '</fieldset>';
		} else {
			// there was no label, so the block used div
			$xhtml .=  '</div>';
		}
		
		// reset tracking properties
		$this->_in_block = false;
		$this->_block_type = null;
		$this->_block_label = null;
		
		// done!
		return $xhtml;
	}
	
	
	/**
	* 
	* Generates the layout to split the layout within a block.
	* 
	* @access protected
	* 
	* @return string The XHTML to split the layout with in a block.
	* 
	*/
	
	protected function _blockSplit()
	{
		// the XHTML to return
		$xhtml = '';
		
		// not already in a block, so don't bother.
		if (! $this->_in_block) {
			return;
		}
		
		// end any group we might already be in
		if ($this->_in_group) {
			$xhtml .= $this->group('end');
		}
		
		// end the current block and start a new one
		switch ($this->_block_type) {
		
		case 'row':
			$xhtml .= '</tr>';
			$xhtml .= $this->_tag('tr');
			break;
		
		case 'col':
			$xhtml .= '</table>';
			$xhtml .= $this->_tag('table');
			break;
		}
		
		// done!
		return $xhtml;
	}
}

?>