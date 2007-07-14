<?php
class Solar_Cli_Help extends Solar_Cli_Base {
    
    protected function _exec($cmd = null)
    {
        if ($cmd) {
            $this->_displayCommandHelp($cmd);
        } else {
            $this->_displayCommandList();
        }
    }
    
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