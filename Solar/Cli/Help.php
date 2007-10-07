<?php
/**
 * 
 * Solar "help" command.
 * 
 * @category Solar
 * 
 * @package Solar_Cli
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Auth.php 2428 2007-04-02 00:44:19Z pmjones $
 * 
 */

/**
 * 
 * Solar "help" command.
 * 
 * @category Solar
 * 
 * @package Solar_Cli
 * 
 */
class Solar_Cli_Help extends Solar_Cli_Base {
    
    /**
     * 
     * Displays a list of help options for a command, or the list of commands
     * if no command was requested.
     * 
     * @param string $cmd The requested command.
     * 
     * @return void
     * 
     */
    protected function _exec($cmd = null)
    {
        if ($cmd) {
            $this->_displayCommandHelp($cmd);
        } else {
            $this->_displayCommandList();
        }
    }
    
    /**
     * 
     * Displays a list of help options for a command, or the list of commands
     * if no command was requested.
     * 
     * @param string $cmd The requested command.
     * 
     * @return void
     * 
     */
    protected function _displayCommandHelp($cmd = null)
    {
        $this->_println();
        
        // the list of known command-to-class mappings
        $list = $this->_console->getCommandList();
        
        // is this a known command?
        if (empty($list[$cmd])) {
            $this->_println('ERR_UNKNOWN_COMMAND', 1, array('cmd' => $cmd));
            return;
        }
        
        $class = $list[$cmd];
        $obj = Solar::factory($class);
        $help = $obj->getInfoHelp();
        if ($help) {
            $this->_println($help);
        } else {
            $this->_println('ERR_NO_HELP');
        }
        
        $this->_println();
        
        $opts = $obj->getInfoOptions();
        if ($opts) {
            
            $this->_println('HELP_VALID_OPTIONS');
            $this->_println();
        
            foreach ($opts as $key => $val) {
                $this->_println($key);
                $val = str_replace("\n", "\n  ", wordwrap(": $val"));
                $this->_println($val);
                $this->_println();
            }
        }
    }
    
    /**
     * 
     * Displays a list of available commands.
     * 
     * @param string $cmd The requested command.
     * 
     * @return void
     * 
     */
    protected function _displayCommandList()
    {
        $this->_println($this->getInfoHelp());
        $this->_println('HELP_AVAILABLE_COMMANDS');
        
        // now get the list of available commands
        $list = $this->_console->getCommandList();
        foreach ($list as $key => $val) {
            $this->_println("    $key");
        }
    }
}