<?php

/**
* 
* Class to track code execution times.
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
* Class to track code execution times.
* 
* @category Solar
* 
* @package Solar
* 
*/

class Solar_Debug_Timer extends Solar_Base {
	
	
	/**
	* 
	* User-provided configuration.
	* 
	* Keys are:
	* 
	* html => (bool) enable/disable encoding output for HTML
	* 
	* @access protected
	* 
	* @var array
	* 
	*/
	
	public $config = array(
		'html' => true
	);
	
	
	/**
	* 
	* Array of time marks.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	protected $marks = array();
	
	
	/**
	* 
	* When we call profile(), what is the longest marker name length?
	* 
	* @access protected
	* 
	* @var boolean
	* 
	*/
	
	protected $maxlen = 0;
	
	
	/**
	* 
	* Solar hooks.
	* 
	* @access public
	* 
	* @param string $hook The hook name to activate.
	* 
	* @return void
	* 
	*/
	
	public function __solar($hook)
	{
		switch ($hook) {
		case 'start':
			$this->start();
			break;
		case 'stop':
			$this->stop();
			break;
		}
	}
	
	
	/**
	* 
	* Resets the profile and marks a new starting time.
	* 
	* @access public
	* 
	*/
	
	public function start()
	{
		$this->marks = array();
		$this->mark('__start');
	}
	
	
	/**
	* 
	* Stop the timer.
	* 
	* @access public
	* 
	*/
	
	public function stop()
	{
		$this->mark('__stop');
	}
	
	
	/**
	* 
	* Mark the time.
	* 
	* @access public
	* 
	* @param string $name Name of the marker to be set
	* 
	* @return void
	* 
	*/
	
	public function mark($name)
	{
		$this->marks[$name] = microtime(true);
	}
	
	
	/**
	* 
	* Returns the time elapsed betweens two marks.
	* 
	* @access public
	* 
	* @param string $start Starting mark; defaults to "__start".
	* 
	* @param string $end Ending mark; if not specified, defaults the the
	* current time.
	* 
	* @return float Time difference between $start and $end.
	* 
	*/
	
	public function diff($start = '__start', $end = null)
	{
		// get the starting time
		$t0 = $this->marks[$start];
		
		// get the ending time
		if (is_null($end)) {
			// no ending mark specified,
			// default to right now.
			$t1 = microtime(true);
		} else {
			// ending mark.
			$t1 = $this->marks[$end];
		}
		
		// compute and return
		return $t1 - $t0;
	}
	
	
	/**
	* 
	* Returns profiling information as an array.
	* 
	* @access public
	* 
	* @return array An array of profile information.
	* 
	*/
	
	public function profile()
	{
		// previous time
		$prev = 0;
		
		// total elapsed time
		$total = 0;
		
		// result array
		$result = array();
		
		// loop through all the marks
		foreach ($this->marks as $name => $time) {
			
			// figure the time difference
			$diff = $time - $prev;
			
			// keep a running total; we always start at zero time.
			if ($name == '__start') {
				$total = 0;
			} else {
				$total = $total + $diff;
			}
			
			// record the profile result for this iteration
			$result[] = array(
				'name'  => $name,
				'time'  => $time,
				'diff'  => $diff,
				'total' => $total
			);
			
			// track the longest marker name
			if (strlen($name) > $this->maxlen) {
				$this->maxlen = strlen($name);
			}
			
			// track the previous time
			$prev = $time;
		}
		
		// by definition, the starting-point time-difference is zero
		$result[0]['diff'] = 0;
		
		// done!
		return $result;
	}
	
	
	/**
	* 
	* Outputs the current profile.
	* 
	* @access public
	* 
	* @param bool $html Whether or not to output encoded for HTML.
	* 
	* @return void
	* 
	*/
	
	public function display($html = null)
	{
		if (is_null($html)) {
			$html = $this->config['html'];
		}
		
		$profile = $this->profile();
		$header = array('mark', 'lap', 'total');
		
		$row = array();
		$row[] = sprintf(
			"%-{$this->maxlen}s : diff     : total",
			'mark'
		);
		
		foreach ($profile as $key => $val) {
			$row[] = sprintf(
				"%-{$this->maxlen}s : %f : %f",
				$val['name'],
				$val['diff'],
				$val['total']
			);
		}
		
		$output = implode("\n", $row);
		
		if ($html) {
			$output = "\n<pre>" . htmlspecialchars($output) . "</pre>\n";
		}
		
		echo $output;
	}
}
?>
