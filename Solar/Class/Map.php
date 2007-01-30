<?php
/**
 * 
 * Creates an array of class-to-file mappings for a class hierarchy.
 * 
 * @category Solar
 * 
 * @package Solar_Class
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Map.php 1186 2006-05-21 15:38:37Z pmjones $
 * 
 */

/**
 * 
 * Creates an array of class-to-file mappings for a class hierarchy.
 * 
 * @category Solar
 * 
 * @package Solar_Class
 * 
 */
class Solar_Class_Map extends Solar_Base {
    
    /**
     * 
     * The class-to-file mappings.
     * 
     * @var array
     * 
     */
    protected $_map = array();
    
    /**
     * 
     * The path to the base of the class hierarchy.
     * 
     * @var array
     * 
     */
    protected $_base;
    
    /**
     * 
     * Gets the class-to-file map for a class hierarchy.
     * 
     * @param string $base The path to the top of the class hierarchy,
     * typically the base PEAR directory.
     * 
     * @param string $class Start mapping with this class.
     * 
     * @return array The class-to-file mappings.
     * 
     */
    public function fetch($base, $class = null)
    {
        // reset the map
        $this->_map = array();
        
        // keep the base path so we know where the class names
        // start (in reference to file names).
        $this->_base =  Solar::fixdir($base);
        
        // if starting with a specific class, add to the path
        // and look for that file specifically.
        if ($class) {
            
            // add to the base path for the file
            $path = $this->_base
                  . str_replace('_', DIRECTORY_SEPARATOR, $class);
            
            // which file would the class be in?
            $file = $path . '.php';
            
            // add the mapping if the file exists
            if (file_exists($file)) {
                $this->_map[$class] = $file;
            }
            
            // append a directory separator so we can descend into
            // the child classes of the requested class.
            $path .= DIRECTORY_SEPARATOR;
            
        } else {
            
            // start at the top of the hierarchy using the "fixed"
            // base path.
            $path = $this->_base;
            
        }
        
        // now build the remaining class-to-file mappings.
        $iter = new RecursiveDirectoryIterator($path);
        $this->_fetch($iter);
        
        // sort by class name, and we're done.
        ksort($this->_map);
        return $this->_map;
    }
    
    /**
     * 
     * Recursively iterates through a directory looking for class files.
     * 
     * Skips CVS directories, and all files and dirs not starting with
     * a capital letter (such as dot-files).
     * 
     * @param RecursiveDirectoryIterator $iter Directory iterator.
     * 
     * @return void
     * 
     */
    protected function _fetch(RecursiveDirectoryIterator $iter)
    {
        for ($iter->rewind(); $iter->valid(); $iter->next()) {
            
            // preliminaries
            $path    = $iter->current()->getPathname();
            $file    = basename($path);
            $capital = ctype_alpha($file[0]) && $file == ucfirst($file);
            $phpfile = strripos($file, '.php');
            
            // check for valid class files
            if ($iter->isDot() || ! $capital) {
                // skip dot-files (including dot-file directories), as
                // well as files/dirs not starting with a capital letter
                continue;
            } elseif ($iter->isDir() && $file == 'CVS') {
                // skip CVS directories
                continue;
            } elseif ($iter->isDir() && $iter->hasChildren()) {
                // descend into child directories
                $this->_fetch($iter->getChildren());
            } elseif ($iter->isFile() && $phpfile) {
                // map the .php file to a class name
                $len   = strlen($this->_base);
                $class = substr($path, $len, -4); // drops .php
                $class = str_replace(DIRECTORY_SEPARATOR, '_', $class);
                $this->_map[$class] = $path;
            }
        }
    }
}