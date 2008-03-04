<?php
/**
 * 
 * Lets you execute a Solar-based script in a separate PHP process, then get
 * back its exit code, last line, and output.
 * 
 * Intended use is for documentation and testing, where you don't want the
 * classes loaded in the main environment to interact with the classes in the
 * current environment.
 * 
 * An example to run `echo "hello world!"` in a separate process:
 * 
 * {{code: php
 *     require_once 'Solar.php';
 *     
 *     $ini = array(
 *         'include_path'    =>  '/path/to/lib',
 *         'error_reporting' =>  E_ALL | E_STRICT,
 *         'error_display'   =>  1,
 *         'html_errors'     =>  0,
 *     );
 *     
 *     $php = Solar::factory('Solar_Php');
 *     
 *     $php->setIniFile(false)
 *         ->setIniArray($ini)
 *         ->setMode('passthru')
 *         ->runCode('echo "hello world!\n"');
 *     
 *     Solar::stop();
 * }}
 * 
 */
class Solar_Php extends Solar_Base {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * @var array
     * 
     */
    protected $_Solar_Php = array(
        'php'          => null,
        'ini_file'     => null,
        'ini_set'      => null,
        'solar_config' => null,
        'mode'         => null,
    );
    
    /**
     * 
     * Command to invoke the PHP binary.
     * 
     * @var string
     * 
     * @todo switch this based on Windows/Mac/Linux.
     * 
     */
    protected $_php = '/usr/local/bin/php';
    
    /**
     * 
     * Which php.ini file to use.
     * 
     * Null means to use the default php.ini file, but false means to use *no*
     * php.ini file.
     * 
     * @var string
     * 
     */
    protected $_ini_file = null;
    
    /**
     * 
     * Override php.ini file settings with these settings.
     * 
     * Format is an array of key-value pairs, where the key is the setting
     * name and the value is the setting value.
     * 
     * @var array
     * 
     */
    protected $_ini_set = array();
    
    /**
     * 
     * When calling Solar::start() in the new process, use this as the $config
     * value.
     * 
     * @var mixed
     * 
     */
    protected $_solar_config = null;
    
    /**
     * 
     * The process execution mode.
     * 
     * Valid settings are:
     * 
     * `echo`
     * : Echoes the command, does not execute it.
     * 
     * `exec`
     * : Uses [[php::exec() | ]] for the process.
     * 
     * `passthru`
     * : Uses [[php::passthru() | ]] for the process.
     * 
     * `shell_exec`
     * : Uses [[php::shell_exec() | ]] for the process.
     * 
     * `system`
     * : Uses [[php::system() | ]] for the process.
     * 
     * @var mixed
     * 
     */
    protected $_mode = 'exec';
    
    /**
     * 
     * After the code runs, each line of output (if any).
     * 
     * @var array
     * 
     */
    protected $_output;
    
    /**
     * 
     * After the code runs, the last line of output (if any).
     * 
     * @var array
     * 
     */
    protected $_last_line;
    
    /**
     * 
     * After the code runs, the exit status code (if any).
     * 
     * Note that null is *not* the same as zero; zero is normally an "OK"
     * exit code, whereas null means "no exit code given".
     * 
     * @var array
     * 
     */
    protected $_exit_code;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config User-defined configuration values.
     * 
     */
    public function __construct($config = null)
    {
        // parent construction
        parent::__construct($config);
        
        // populate each of these properties with its config value ...
        $list = array_keys($this->_Solar_Php);
        foreach ($list as $key) {
            // ... but only if not null.
            if ($this->_config[$key] !== null) {
                $var = "_$key";
                $this->$var = $this->_config[$key];
            }
        }
    }
    
    /**
     * 
     * Sets the PHP command to call at the command line.
     * 
     * @param string $php The PHP command; e.g., "/usr/local/php".
     * 
     * @return Solar_Php
     * 
     */
    public function setPhp($php)
    {
        $this->_php = $php;
        return $this;
    }
    
    /**
     * 
     * Sets the location of the php.ini file to use.
     * 
     * If null, uses the default php.ini file location.
     * 
     * If false, uses *no* php.ini file (the `--no-php-ini` switch).
     * 
     * @param string $file The php.ini file location.
     * 
     * @return Solar_Php
     * 
     */
    public function setIniFile($file)
    {
        if ($file !== null && $file !== false) {
            $file = (string) $file;
        }
        $this->_ini_file = $file;
        return $this;
    }
    
    /**
     * 
     * Sets one php.ini value, overriding the php.ini file.
     * 
     * @param string $key The php.ini setting name.
     * 
     * @param string $val The php.ini setting value.
     * 
     * @return Solar_Php
     * 
     */
    public function setIniVal($key, $val)
    {
        $this->_ini_set[$key] = $val;
        return $this;
    }
    
    /**
     * 
     * Sets an array of php.ini values, overriding the php.ini file.
     * 
     * Each key in the array is a php.ini setting name, and each value is the
     * corresponding php.ini value.
     * 
     * @param string $list The array of php.ini key-value pairs.
     * 
     * @return Solar_Php
     * 
     */
    public function setIniArray($list)
    {
        foreach ($list as $key => $val) {
            $this->_ini_set[$key] = $val;
        }
        return $this;
    }
    
    /**
     * 
     * Sets an array of php.ini values, overriding the php.ini file.
     * 
     * Each key in the array is a php.ini setting name, and each value is the
     * corresponding php.ini value.
     * 
     * @param string $list The array of php.ini key-value pairs.
     * 
     * @return Solar_Php
     * 
     */
    public function setSolarConfig($solar_config)
    {
        $this->_solar_config = $solar_config;
        return $this;
    }
    
    /**
     * 
     * Sets the execution mode for the process.
     * 
     * Valid modes are:
     * 
     * `echo`
     * : Echoes the command, does not execute it.
     * 
     * `exec`
     * : Uses [[php::exec() | ]] for the process.
     * 
     * `passthru`
     * : Uses [[php::passthru() | ]] for the process.
     * 
     * `shell_exec`
     * : Uses [[php::shell_exec() | ]] for the process.
     * 
     * `system`
     * : Uses [[php::system() | ]] for the process.
     * 
     * @param string $mode One of the reconized modes.
     * 
     * @return Solar_Php
     * 
     */
    public function setMode($mode)
    {
        $list = array('echo', 'exec', 'passthru', 'shell_exec', 'system');
        if (! in_array($mode, $list)) {
            throw $this->_exception('ERR_UNKNOWN_MODE');
        } else {
            $this->_mode = $mode;
        }
        return $this;
    }
    
    /**
     * 
     * Runs the named file as the PHP code for the process.
     * 
     * @param string $file The script file name.
     * 
     * @return Solar_Php
     * 
     */
    public function run($file)
    {
        $code = file_get_contents($file);
        return $this->run($code);
    }
    
    /**
     * 
     * Runs the given string as the PHP code for the process.
     * 
     * @param string $code The script code.
     * 
     * @return Solar_Php
     * 
     */
    public function runCode($code)
    {
        // clean up from last run
        $this->_output    = array();
        $this->_last_line = null;
        $this->_exit_code = null;
        
        // build the full command with PHP code
        $cmd = $this->_buildCommand() . " --run " . $this->_buildCode($code);
        
        // what execution mode?
        switch ($this->_mode) {
        case 'echo':
            echo $cmd;
            break;
        case 'exec':
            $this->_last_line = exec($cmd, $this->_output, $this->_exit_code);
            break;
        case 'passthru':
            passthru($cmd, $this->_exit_code);
            break;
        case 'shell_exec':
            $this->_output = shell_exec($cmd);
            $this->_last_line = end($this->_output);
            reset($this->_output);
            break;
        case 'system':
            $this->_last_line = system($cmd, $this->_exit_code);
            break;
        }
        
        // done!
        return $this;
    }
    
    /**
     * 
     * Gets the exit code from the separate process.
     * 
     * @return int
     * 
     */
    public function getExitCode()
    {
        return $this->_exit_code;
    }
    
    /**
     * 
     * Gets all lines of output from the separate process.
     * 
     * @return array
     * 
     */
    public function getOutput()
    {
        return $this->_output;
    }
    
    /**
     * 
     * Gets the last line of output from the separate process.
     * 
     * @return string
     * 
     */
    public function getLastLine()
    {
        return $this->_last_line;
    }
    
    /**
     * 
     * Wraps the given code string in extra code to load, start, and stop
     * Solar.
     * 
     * @param string $code The code to run in the separate process.
     * 
     * @return string
     * 
     */
    protected function _buildCode($code)
    {
        // strip long opening tag
        if (substr($code, 0, 5) == '<?php') {
            $code = substr($code, 5);
        }
        
        // strip short opening tag
        if (substr($code, 0, 2) == '<?') {
            $code = substr($code, 2);
        }
        
        // strip closing tag
        if (substr($code, -2) == '?>') {
            $code = substr($code, 0, -2);
        }
        
        // get the solar config as a variable
        $solar_config = var_export($this->_solar_config, true);
        
        // wrap the code in Solar::start() and Solar::stop()
        $code = "require 'Solar.php'; "
              . "Solar::start($solar_config); "
              . "$code; "
              . "Solar::stop();";
        
        // escape for shell, and done
        return escapeshellarg($code);
    }
    
    /**
     * 
     * Builds the command-line invocation of PHP.
     * 
     * @return string The PHP command with the necessary switches.
     * 
     */
    protected function _buildCommand()
    {
        // the PHP binary
        $cmd = $this->_php;
        
        // using a php.ini file?
        if ($this->_ini_file) {
            // non-default file or path
            $cmd .= " --php-ini " . escapeshellarg($this->_ini_file);
        } elseif ($this->_ini_file === false) {
            // explicitly *no* file ot be used
            $cmd .= " --no-php-ini";
        }
        
        // override php.ini values
        foreach ((array) $this->_ini_set as $key => $val) {
            $key = escapeshellarg($key);
            $val = escapeshellarg($val);
            $cmd .= " --define $key=$val";
        }
        
        return $cmd;
    }
}
