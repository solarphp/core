<?php
class Solar_Cli_RunTests extends Solar_Cli_Base {
    
    protected function _exec($class = null)
    {
        Solar::dump($this->_options);
        
        // look for a test directory, otherwise assume that the tests are
        // in the same dir.
        $dir = $this->_options['dir'];
        if (! $dir) {
            $dir = getcwd();
        }
        
        // make sure it matches the OS
        $dir = Solar::fixdir($dir);
        
        // make sure it ends in "/Test/".
        $end = DIRECTORY_SEPARATOR . 'Test' . DIRECTORY_SEPARATOR;
        if (substr($dir, -5) != $end) {
            $dir = rtrim($dir, DIRECTORY_SEPARATOR) . $end;
        }
        
        // run just the one test?
        $only = (bool) $this->_options['only'];
        
        // do we have an include_path?
        $include_path = get_include_path();
        if ($this->_options['include_path']) {
            set_include_path(
                $this->_options['include_path']
                . PATH_SEPARATOR
                . $include_path
            );
        }
        
        Solar::dump(get_include_path());
        
        // set up a test suite object 
        $suite = Solar::factory('Solar_Test_Suite', array(
            'dir' => $dir,
            'error_reporting' => E_ALL | E_STRICT,
        ));
        
        // run the suite
        $suite->run($class, $only);
        
        // put hte include-path back
        set_include_path($include_path);
    }
}
