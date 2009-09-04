<?php
/**
 * 
 * The CLI equivalent of a page-controller; a single command to be invoked
 * from the command-line.
 * 
 * @category Solar
 * 
 * @package Solar_Controller
 * 
 * @author Clay Loveless <clay@killersoft.com>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Controller_Command extends Solar_Base
{
    /**
     * 
     * Array of format conversions for use on a variety of pre-set console
     * style combinations.
     * 
     * Based on ANSI VT100 Color/Style Codes, according to the [VT100 User Guide][1]
     * and the [ANSI/VT100 Terminal Control reference][2]. Inspired by
     * [PEAR Console_Color][3].
     * 
     * [1]: http://vt100.net/docs/vt100-ug
     * [2]: http://www.termsys.demon.co.uk/vtansi.htm
     * [3]: http://pear.php.net/Console_Color
     * 
     * @var array
     * 
     */
    protected $_vt100 = array(
        // literal percent sign
        '%%'    => '%',             // percent-sign
        // color, normal weight
        '%k'    => "\033[30m",      // black
        '%r'    => "\033[31m",      // red
        '%g'    => "\033[32m",      // green
        '%y'    => "\033[33m",      // yellow
        '%b'    => "\033[34m",      // blue
        '%m'    => "\033[35m",      // magenta/purple
        '%p'    => "\033[35m",      // magenta/purple
        '%c'    => "\033[36m",      // cyan/light blue
        '%w'    => "\033[37m",      // white
        '%n'    => "\033[0m",       // reset to terminal default
        // color, bold
        '%K'    => "\033[30;1m",    // black, bold
        '%R'    => "\033[31;1m",    // red, bold
        '%G'    => "\033[32;1m",    // green, bold
        '%Y'    => "\033[33;1m",    // yellow, bold
        '%B'    => "\033[34;1m",    // blue, bold
        '%M'    => "\033[35;1m",    // magenta/purple, bold
        '%P'    => "\033[35;1m",    // magenta/purple, bold
        '%C'    => "\033[36;1m",    // cyan/light blue, bold
        '%W'    => "\033[37;1m",    // white, bold
        '%N'    => "\033[0;1m",     // terminal default, bold
        // background color
        '%0'    => "\033[40m",      // black background
        '%1'    => "\033[41m",      // red background
        '%2'    => "\033[42m",      // green background
        '%3'    => "\033[43m",      // yellow background
        '%4'    => "\033[44m",      // blue background
        '%5'    => "\033[45m",      // magenta/purple background
        '%6'    => "\033[46m",      // cyan/light blue background
        '%7'    => "\033[47m",      // white background
        // assorted style shortcuts
        '%F'    => "\033[5m",       // blink/flash
        '%_'    => "\033[5m",       // blink/flash
        '%U'    => "\033[4m",       // underline
        '%I'    => "\033[7m",       // reverse/inverse
        '%*'    => "\033[1m",       // bold
        '%d'    => "\033[2m",       // dim        
    );
    
    /**
     * 
     * Option flags and values extracted from the command-line arguments.
     * 
     * @var array
     * 
     */
    protected $_options = array();
    
    /**
     * 
     * A Solar_Getopt object to manage options and parameters.
     * 
     * @var Solar_Getopt
     * 
     */
    protected $_getopt;
    
    /**
     * 
     * The Solar_Controller_Console object (if any) that invoked this command.
     * 
     * @var Solar_Controller_Console
     * 
     */
    protected $_console;
    
    /**
     * 
     * File handle pointing to STDOUT for normal output.
     * 
     * @var resource
     * 
     */
    protected $_stdout;
    
    /**
     * 
     * File handle pointing to STDERR for error output.
     * 
     * @var resource
     * 
     */
    protected $_stderr;
    
    /**
     * 
     * Post-construction tasks to complete object construction.
     * 
     * @return void
     * 
     */
    protected function _postConstruct()
    {
        parent::_postConstruct();
        
        // stdout and stderr
        $this->_stdout = fopen('php://stdout', 'w');
        $this->_stderr = fopen('php://stderr', 'w');
        
        // set the recognized options
        $options = $this->_getOptionSettings();
        $this->_getopt = Solar::factory('Solar_Getopt');
        $this->_getopt->setOptions($options);
        
        // follow-on setup
        $this->_setup();
    }
    
    /**
     * 
     * Destructor; closes STDOUT and STDERR file handles.
     * 
     * @return void
     * 
     */
    public function __destruct()
    {
        fclose($this->_stdout);
        fclose($this->_stderr);
    }
    
    /**
     * 
     * Injects the console-controller object (if any) that invoked this command.
     * 
     * @param Solar_Controller_Console $console The console controller.
     * 
     * @return void
     * 
     */
    public function setConsoleController($console)
    {
        $this->_console = $console;
    }
    
    /**
     * 
     * Public interface to execute the command.
     * 
     * This method...
     * 
     * - populates and validates the option values
     * - calls _preExec()
     * - calls _exec() with the numeric parameters from the options
     * - calls _postExec()
     * 
     * @param array $argv The command-line arguments from the user.
     * 
     * @return void
     * 
     * @todo Accept a Getopt object in addition to $argv array?
     * 
     */
    public function exec($argv = null)
    {
        // get the command-line arguments
        if ($argv === null) {
            // use the $_SERVER values
            $argv = $this->_request->server['argv'];
            // remove the argument pointing to this command
            array_shift($argv);
        } else {
            $argv = (array) $argv;
        }
        
        // set options, populate values, and validate parameters
        $this->_getopt->populate($argv);
        if (! $this->_getopt->validate()) {
            // need a better way to throw exceptions with specific error
            // messages
            throw $this->_exception(
                'ERR_INVALID_OPTIONS',
                $this->_getopt->getInvalid()
            );
        }
        
        // retain the option values, minus the numeric params
        $this->_options = $this->_getopt->values();
        $params = array();
        foreach ($this->_options as $key => $val) {
            if (is_int($key)) {
                $params[] = $val;
                unset($this->_options[$key]);
            }
        }
        
        // call pre-exec
        $skip_exec = $this->_preExec();
        
        // should we skip the main execution?
        if ($skip_exec !== true) {
            // call _exec() with the numeric params from getopt
            call_user_func_array(
                array($this, '_exec'),
                $params
            );
        }
        
        // call post-exec, and we're done
        $this->_postExec();
    }
    
    /**
     * 
     * Returns an array of option flags and descriptions for this command.
     * 
     * @return array An associative array where the key is the short + long
     * option forms, and the value is the description for the option.
     * 
     */
    public function getInfoOptions()
    {
        $options = array();
        foreach ($this->_getopt->options as $name => $info) {
            
            $key = null;
            
            if ($info['short']) {
                $key .= "-" . $info['short'];
            }
            
            if ($key && $info['long']) {
                $key .= " | --" . $info['long'];
            } else {
                $key .= "--" . $info['long'];
            }
            
            $options[$key] = $info['descr'];
        }
        
        ksort($options);
        return $options;
    }
    
    /**
     * 
     * Returns the help text for this command.
     * 
     * @return string The contents of "Info/help.txt" for this class, or null
     * if the file does not exist.
     * 
     */
    public function getInfoHelp()
    {
        // what would its help file be named?
        $class = get_class($this);
        $file = str_replace('_', DIRECTORY_SEPARATOR, $class)
              . DIRECTORY_SEPARATOR . 'Info'
              . DIRECTORY_SEPARATOR . 'help.txt';
        
        // does that file exist?
        $file = Solar_File::exists($file);
        if ($file) {
            return file_get_contents($file);
        }
    }
    
    /**
     * 
     * Gets the option settings from the class hierarchy.
     * 
     * @return array
     * 
     */
    protected function _getOptionSettings()
    {
        // the options to be set
        $options = array();
        
        // find the parents of this class, including this class
        $parents = Solar_Class::parents(get_class($this), true);
        array_shift($parents);
        array_shift($parents);
        
        // get options.php for each parent class, as well as this class
        foreach ($parents as $class) {
            
            $file  = str_replace('_', DIRECTORY_SEPARATOR, $class)
                    . DIRECTORY_SEPARATOR . 'Info'
                    . DIRECTORY_SEPARATOR . 'options.php';
            
            $file  = Solar_File::exists($file);
            
            if ($file) {
                $options = array_merge(
                    $options, 
                    (array) include $file
                );
            }
        }
        
        return $options;
    }
    
    /**
     * 
     * Escapes ASCII control codes (0-31, 127) and %-signs.
     * 
     * Note that this will catch newlines and carriage returns as well.
     * 
     * @param string $text The text to escape.
     * 
     * @return string The escaped text.
     * 
     */
    protected function _escape($text)
    {
        static $list;
        if (! $list) {
            $list = array(
                '%' => '%%',
            );
            
            for ($i = 0; $i < 32; $i ++) {
                $list[chr($i)] = "\\$i";
            }
            
            $list[chr(127)] = "\\127";
        }
        
        return strtr($text, $list);
    }
    
    /**
     * 
     * Prints text to STDOUT **without** a trailing newline.
     * 
     * If the text is a locale key, that text will be used instead.
     * 
     * Automatically replaces style-format codes for VT100 shell output.
     * 
     * @param string $text The text to print to STDOUT, usually a translation
     * key.
     * 
     * @param mixed $num Helps determine whether to get a singular
     * or plural translation.
     * 
     * @param array $replace An array of replacement values for the string.
     * 
     * @return void
     * 
     */
    protected function _out($text = null, $num = 1, $replace = null)
    {
        fwrite(
            $this->_stdout,
            $this->_vt100($text, $num, $replace)
        );
    }
    
    /**
     * 
     * Prints text to STDOUT and appends a newline.
     * 
     * If the text is a locale key, that text will be used instead.
     * 
     * Automatically replaces style-format codes for VT100 shell output.
     * 
     * @param string $text The text to print to STDOUT, usually a translation
     * key.
     * 
     * @param mixed $num Helps determine whether to get a singular
     * or plural translation.
     * 
     * @param array $replace An array of replacement values for the string.
     * 
     * @return void
     * 
     */
    protected function _outln($text = null, $num = 1, $replace = null)
    {
        fwrite(
            $this->_stdout,
            $this->_vt100($text, $num, $replace) . PHP_EOL
        );
    }
    
    /**
     * 
     * Prints text to STDERR **without** a trailing newline.
     * 
     * If the text is a locale key, that text will be used instead.
     * 
     * Automatically replaces style-format codes for VT100 shell output.
     * 
     * @param string $text The text to print to STDERR, usually a translation
     * key.
     * 
     * @param mixed $num Helps determine whether to get a singular
     * or plural translation.
     * 
     * @param array $replace An array of replacement values for the string.
     * 
     * @return void
     * 
     */
    protected function _err($text = null, $num = 1, $replace = null)
    {
        fwrite(
            $this->_stderr,
            $this->_vt100($text, $num, $replace)
        );
    }
    
    /**
     * 
     * Prints text to STDERR and appends a newline.
     * 
     * If the text is a locale key, that text will be used instead.
     * 
     * Automatically replaces style-format codes for VT100 shell output.
     * 
     * @param string $text The text to print to STDERR, usually a translation
     * key.
     * 
     * @param mixed $num Helps determine whether to get a singular
     * or plural translation.
     * 
     * @param array $replace An array of replacement values for the string.
     * 
     * @return void
     * 
     */
    protected function _errln($text = null, $num = 1, $replace = null)
    {
        fwrite(
            $this->_stderr,
            $this->_vt100($text, $num, $replace) . PHP_EOL
        );
    }
    
    /**
     * 
     * Frontend to locale() that replaces style-format codes for VT100 shell
     * output.
     * 
     * @param string $text The text for printing, usually a translation
     * key.
     * 
     * @param mixed $num Helps determine whether to get a singular
     * or plural translation.
     * 
     * @param array $replace An array of replacement values for the string.
     * 
     * @return string The localized string with VT100 shell codes.
     * 
     */
    protected function _vt100($text, $num, $replace)
    {
        return strtr(
            $this->locale($text, $num, $replace),
            $this->_vt100
        );
    }
    
    /**
     * 
     * Post-construction setup logic.
     * 
     * @return void
     * 
     */
    protected function _setup()
    {
    }
    
    /**
     * 
     * Runs just before the main _exec() method.
     * 
     * @return bool True to skip _exec(), null otherwise.
     * 
     */
    protected function _preExec()
    {
    }
    
    /**
     * 
     * The main command method.
     * 
     * @return void
     * 
     */
    protected function _exec()
    {
    }
    
    /**
     * 
     * Runs just after the main _exec() method.
     * 
     * @return void
     * 
     */
    protected function _postExec()
    {
    }
}