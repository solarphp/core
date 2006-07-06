<?php
/**
 * 
 * Class to help examine and debug variables.
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
 * Class to help examine and debug variables.
 * 
 * @category Solar
 * 
 * @package Solar_Debug
 * 
 * @todo Add reflect() method for reflection capture?
 * 
 */
class Solar_Debug_Var extends Solar_Base {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are:
     * 
     * : \\output\\ : (string) Output mode.  Set to 'html' for HTML; 
     *   or 'text' for plain text.  Default autodetects by SAPI version.
     * 
     * @var array
     * 
     */
    protected $_Solar_Debug_Var = array(
        'output' => null,
    );
    
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        if (empty($this->_config['output'])) {
            $mode = (PHP_SAPI == 'cli') ? 'text' 
                                        : 'html';
            $this->_config['output'] = $mode;
        }
    }
    
    /**
     * 
     * Returns the output of "var_dump()" with a label.
     * 
     * Buffers the [[php var_dump]] for a variable,
     * applies some simple formatting for readability,
     * and [[php echo]]s it with an optional label.
     * Use this for debugging variables to see exactly
     * what they contain.
     * 
     * @param mixed &$var The variable to dump.
     * 
     * @param string $label A label to prefix to the dump.
     * 
     * @return string The labeled results of var_dump().
     * 
     */
    public function dump(&$var, $label = null)
    {
        // if there's a label, add a space after it
        if (trim($label) != '') {
            $label .= ' ';
        }
        
        // dump the label and variable into a buffer
        // and keep the output
        ob_start();
        echo $label;
        var_dump($var);
        $output = ob_get_clean();
        
        // pretty up the newlines and indents
        $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
        
        // was this for HTML?
        if (strtolower($this->_config['output']) == 'html') {
            $output = '<pre>' . htmlspecialchars($output) . '</pre>';
        }
        
        // done
        return $output;
    }
}
?>