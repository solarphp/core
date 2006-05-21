<?php
/**
 * 
 * Class to track code execution times.
 * 
 * @category Solar
 * 
 * @package Solar_Debug
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
 * @package Solar_Debug
 * 
 */
class Solar_Debug_Timer extends Solar_Base {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are:
     * 
     * : \\html\\ : (bool) enable/disable encoding output for HTML
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'locale'       => 'Solar/Debug/Locale/',
        'output'       => 'html',
        'auto_start'   => false,
        'auto_display' => false,
    );
    
    /**
     * 
     * Array of time marks.
     * 
     * @var array
     * 
     */
    protected $_marks = array();
    
    /**
     * 
     * The longest marker name length shown in profile().
     * 
     * @var boolean
     * 
     */
    protected $_maxlen = 8;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-provided configuration values.
     * If the 'auto_start' key is true, this will start the timer.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        if ($this->_config['auto_start']) {
            $this->start();
        }
    }
    
    /**
     * 
     * Destructor.
     * 
     * If the 'auto_display' config key is true, this will display the profile.
     * 
     * @return void
     * 
     */
    public function __destruct()
    {
        if ($this->_config['auto_display']) {
            $this->display();
        }
    }
    
    
    /**
     * 
     * Resets the profile and marks a new starting time.
     * 
     * This resets the profile and adds a new mark labeled
     * \\__start\\.  Use it to start the timer.
     * 
     * @return void
     * 
     */
    public function start()
    {
        $this->_marks = array();
        $this->mark('__start');
    }
    
    /**
     * 
     * Stops the timer.
     * 
     * Use this to stop the timer, marking the time with the label \\__stop\\.
     * 
     * @return void
     * 
     */
    public function stop()
    {
        $this->mark('__stop');
    }
    
    /**
     * 
     * Marks the time.
     * 
     * Use this to mark the profile to see how much time has
     * elapsed since the last mark.  Labels do not have to be
     * unique, but should be distinctive enough so you can tell
     * which one is which on long profiles.
     * 
     * @param string $name Name of the marker to be set
     * 
     * @return void
     * 
     */
    public function mark($name)
    {
        $this->_marks[$name] = microtime(true);
    }
    
    /**
     * 
     * Returns profiling information as an array.
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
        foreach ($this->_marks as $name => $time) {
            
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
            if (strlen($name) > $this->_maxlen) {
                $this->_maxlen = strlen($name);
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
     * This dumps the profile information as a table; see the
     * [HomePage home page for this class] for an example.
     * 
     * @param string $title A title for the output.
     * 
     * @return void
     * 
     */
    public function display($title = null)
    {
        // get the profile info
        $profile = $this->profile();
        
        // format the localized column names
        $colname = array(
            'name'  => $this->locale('LABEL_NAME'),
            'time'  => $this->locale('LABEL_TIME'),
            'diff'  => $this->locale('LABEL_DIFF'),
            'total' => $this->locale('LABEL_TOTAL')
        );
        
        foreach ($colname as $key => $val) {
            // reduce to max 8 chars
            $val = substr($val, 0, 8);
            // pad to 8 spaces
            $colname[$key] = str_pad($val, 8);
        }
        
        // prep the output rows
        $row = array();
        
        // add a title
        if (trim($title != '')) {
            $row[] = $title;
        }
        
        // add the column names
        $row[] = sprintf(
            "%-{$this->_maxlen}s : {$colname['diff']} : {$colname['total']}",
            $colname['name']
        );
        
        // add each timer mark
        foreach ($profile as $key => $val) {
            $row[] = sprintf(
                "%-{$this->_maxlen}s : %f : %f",
                $val['name'],
                $val['diff'],
                $val['total']
            );
        }
        
        // finalize output and display
        $output = implode("\n", $row);
        
        if ($this->_config['output'] == 'html') {
            $output = '<pre>' . htmlspecialchars($output) . '</pre>';
        }
        
        echo $output;
    }
}
?>