<?php
/**
 * 
 * Base Solar command class.
 * 
 * @category Solar
 * 
 * @package Solar_Cli
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Cli_Base extends Solar_Controller_Command
{
    /**
     * 
     * Displays a "command not recognized" message.
     * 
     * Extends this class and override _exec() to get real functionality.
     * 
     * @args string $cmd The requested command.
     * 
     * @return void
     * 
     */
    protected function _exec()
    {
        $args = func_get_args();
        if ($args) {
            // we use 'null' command because we could have gotten here from
            // using an option or something else first, and a recognized 
            // command later on.  showing that command here would be confusing.
            $this->_outln('ERR_UNKNOWN_COMMAND', 1, array('cmd' => null));
            $this->_outln('HELP_TRY_SOLAR_HELP');
        } else {
            $this->_outln($this->getInfoHelp());
        }
    }
    
    /**
     * 
     * Pre-exec logic.
     * 
     * Catches --version and -v to display version information and exit.
     * 
     * @return bool True to skip _exec(), false otherwise.
     * 
     */
    protected function _preExec()
    {
        $skip_exec = false;
        
        switch (true) {
        
        case $this->_options['version']:
            $this->_out("Solar command-line tool, version ");
            $this->_outln($this->apiVersion() . '.');
            $skip_exec = true;
            break;
        
        }
        
        return $skip_exec;
    }
    
    /**
     * 
     * Post-execution logic.
     * 
     * @return void
     * 
     */
    protected function _postExec()
    {
        parent::_postExec();
        
        // return terminal to normal colors
        $this->_out("%n");
    }
}

