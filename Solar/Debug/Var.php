<?php
/**
 * 
 * Class to help examine and debug variables.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_Debug
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
 * Class to help examine and debug variables.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @subpackage Solar_Debug
 * 
 * @todo add reflect() method for reflection capture
 * 
 */
class Solar_Debug_Var extends Solar_Base {
    
    /**
     * 
     * User-provided configuration.
     * 
     * Keys are:
     * 
     * output => (string) Output mode.  Default is 'html'; anything else is
     * treated as 'text' (plain text).
     * 
     * @access protected
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'output' => 'html',
    );
    
    /**
     * 
     * Captures the output of "var_dump()" with a label.
     * 
     * @access public
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
        if ($this->_config['output'] == 'html') {
            $output = '<pre>' . htmlspecialchars($output) . '</pre>';
        }
        
        // done
        return $output;
    }
}
?>