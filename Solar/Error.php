<?php

/**
* 
* Error class; returns one or more errors.
* 
* @category Solar
* 
* @package Solar
* 
* @author Paul M. Jones <pmjones@solarphp.com>
* 
* @license LGPL
* 
* @version $Id$
* 
*/

/**
* 
* Error class; returns one or more errors.
* 
* Usage:
* 
* <code>
* // to report one error:
* return Solar::error($errCode, $errText, $errInfo);
* 
* // to report multiple errors, you can add to
* // an existing error...
* $err = Solar::error($code[0], $text[0], $info[0]);
* $err->push($code[1], $text[1], $info[1]);
* return $err;
* 
* // ... or push as you go.
* $err = Solar::object('Solar_Error');
* $err->push($code[0], $text[0], $info[0]);
* $err->push($code[1], $text[1], $info[1]);
* return $err;
* 
* // Then, when checking for errors:
* $result = $class->method();
* 
* if (Solar::isError($result)) {
* 
*     // grab only one error:
*     $err = $result->pop();
*     print_r($err);
*     
*     // or grab multiple errors:
*     while ($err = $result->pop()) {
*         print_r($err);
*     }
*     
*     // or be lazy:
*     echo $result;
*     die();
* }
* </code>
* 
* @category Solar
* 
* @package Solar
* 
* @todo Make this object observable?
* 
*/

class Solar_Error extends Solar_Base {
	
	
	/**
	* 
	* User-provided configuration.
	* 
	* Keys are:
	* 
	* push_callback => (string|array) The callback when you call push().
	* 
	* pop_callback => (string|array) The callback when you call pop().
	* 
	* trace => (bool) Whether or not to debug_backtrace() errors.
	* 
	* level => (int) Default error level, e.g. E_USER_NOTICE.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $config = array(
		'push_callback' => null,
		'pop_callback'  => null,
		'trace' => true,
		'level' => E_USER_NOTICE
	);
	
	
	/**
	* 
	* Stack of errors.
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	protected $stack = array();
	
	
	/**
	* 
	* Constructor.
	* 
	*/
	
	public function __construct($config = null)
	{
		$this->config['push_callback'] = array($this, 'pushCallback');
		parent::__construct($config);
	}
	
	
	/**
	* 
	* Pushes an error onto the stack.
	* 
	* @access public
	* 
	* @param string $class The class that generated the error.
	* 
	* @param mixed $code A scalar error code, or a Solar_Error object.
	* If a Solar_Error object, the errors in its stack are shifted onto the
	* local stack.
	* 
	* @param string $text An error message.
	* 
	* @param array $info An array of other information about the error,
	* generally associative.
	* 
	* @param int $level An error severity level code; e.g., E_USER_NOTICE.
	* 
	* @param bool $trace Whether or not to record a debug_backtrace().
	* 
	* @return void
	* 
	*/
	
	public function push($class, $code, $text = '', $info = array(),
		$level = null, $trace = null)
	{
		// is the code an extant error object?  if so,
		// capture its stack onto our stack.
		if (Solar::isError($code)) {
			while($err = $code->pop()) {
				// use unshift instead of push to make sure
				// the order ends up the same in both stacks.
				array_unshift($this->stack, $err);
			}
			// callbacks will already have been effected
			return;
		}
		
		// set default level
		if (is_null($level)) {
			$level = $this->config['level'];
		}
		
		// set default trace
		if (is_null($trace)) {
			$trace = $this->config['trace'];
		}
		
		// prepare the error array
		$err = array(
			'class'       => $class,
			'code'        => $code,
			'text'        => $text,
			'info'        => $info,
			'level'       => $level,
			'class::code' => $class . '::' . $code,
			'trace'       => $trace ? debug_backtrace() : null
		);
		
		// push the error array onto the stack ...
		array_push($this->stack, $err);
		
		// ... and make the callback.
		if (! empty($this->config['push_callback'])) {
			call_user_func($this->config['push_callback'], $err, $this);
		}
	}
	
	
	/**
	* 
	* Pops an error off the stack.
	* 
	* @access public
	* 
	* @return array An array of error information.
	* 
	*/
	
	public function pop()
	{
		$err = @array_pop($this->stack);
		
		if ($err) {
			$err['count'] = $this->count(); // number of remaining errors
		}
		
		// make the callback and return the error.
		if (! empty($this->config['pop_callback'])) {
			call_user_func($this->config['pop_callback'], $err, $this);
		}
		return $err;
	}
	
	
	/**
	* 
	* Returns a count of how many errors are on the stack.
	* 
	* @access public
	* 
	* @return int The number of errors on the stack.
	* 
	*/
	
	public function count()
	{
		return count($this->stack);
	}
	
	
	/**
	* 
	* A naive push callback.
	* 
	* Will print out WARNINGs and ERRORs, will die() on ERRORs.
	* 
	* @access protected
	* 
	* @param array $err An array of error information just pushed onto the
	* stack.
	* 
	* @param object $obj The Solar_Error object that $err was just pushed
	* into.
	* 
	* @return void
	* 
	*/
	
	protected function pushCallback($err, $obj)
	{
		if ($err['level'] == E_USER_WARNING || $err['level'] == E_WARNING) {
			Solar::dump($err);
		}
		
		if ($err['level'] == E_USER_ERROR || $err['level'] == E_ERROR) {
			while (ob_get_level()) {
				ob_end_flush();
			}
			echo $obj;
			die();
		}
	}
	
	
	/**
	* 
	* Pops-and-prints each error on the stack.
	* 
	*/
	
	public function __toString()
	{
		ob_start();
		while ($err = $this->pop()) {
			Solar::dump($err);
		}
		return ob_get_clean();
	}
}
?>