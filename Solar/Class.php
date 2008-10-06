<?php
/**
 * 
 * Staic support methods for class information.
 * 
 * @category Solar
 * 
 * @package Solar
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */
class Solar_Class
{
    /**
     * 
     * Parent hierarchy for all classes.
     * 
     * We keep track of this so configs, locale strings, etc. can be
     * inherited properly from parent classes, and so we don't need to
     * recalculate it on each request.
     * 
     * @var array
     * 
     */
    protected static $_parents = array();
    
    /**
     * 
     * Loads a class or interface file from the include_path.
     * 
     * Thanks to Robert Gonzalez  for the report leading to this method.
     * 
     * @param string $name A Solar (or other) class or interface name.
     * 
     * @return void
     * 
     * @todo Add localization for errors
     * 
     */
    public static function autoload($name)
    {
        // did we ask for a non-blank name?
        if (trim($name) == '') {
            throw Solar::exception(
                'Solar_Class',
                'ERR_AUTOLOAD_EMPTY',
                'No class or interface named for loading.',
                array('name' => $name)
            );
        }
        
        // pre-empt further searching for the named class or interface.
        // do not use autoload, because this method is registered with
        // spl_autoload already.
        $exists = class_exists($name, false)
               || interface_exists($name, false);
        
        if ($exists) {
            return;
        }
        
        // convert the class name to a file path.
        $file = str_replace('_', DIRECTORY_SEPARATOR, $name) . '.php';
        
        // include the file and check for failure. we use Solar_File::load()
        // instead of require() so we can see the exception backtrace.
        Solar_File::load($file);
        
        // if the class or interface was not in the file, we have a problem.
        // do not use autoload, because this method is registered with
        // spl_autoload already.
        $exists = class_exists($name, false)
               || interface_exists($name, false);
        
        if (! $exists) {
            throw Solar::exception(
                'Solar_Class',
                'ERR_AUTOLOAD_FAILED',
                'Class or interface does not exist in loaded file',
                array('name' => $name, 'file' => $file)
            );
        }
    }
    
    /**
     * 
     * Returns an array of the parent classes for a given class.
     * 
     * @param string|object $spec The class or object to find parents
     * for.
     * 
     * @param bool $include_class If true, the class name is element 0,
     * the parent is element 1, the grandparent is element 2, etc.
     * 
     * @return array
     * 
     */
    public static function parents($spec, $include_class = false)
    {
        if (is_object($spec)) {
            $class = get_class($spec);
        } else {
            $class = $spec;
        }
        
        // do we need to load the parent stack?
        if (empty(Solar_Class::$_parents[$class])) {
            // use SPL class_parents(), which uses autoload by default.  use
            // only the array values, not the keys, since that messes up BC.
            $parents = array_values(class_parents($class));
            Solar_Class::$_parents[$class] = array_reverse($parents);
        }
        
        // get the parent stack
        $stack = Solar_Class::$_parents[$class];
        
        // add the class itself?
        if ($include_class) {
            $stack[] = $class;
        }
        
        // done
        return $stack;
    }
    
    /**
     * 
     * Returns the directory for a specific class, plus an optional
     * subdirectory path.
     * 
     * @param string|object $spec The class or object to find parents
     * for.
     * 
     * @param string $sub Append this subdirectory.
     * 
     * @return string The class directory, with optional subdirectory.
     * 
     */
    public static function dir($spec, $sub = null)
    {
        if (is_object($spec)) {
            $class = get_class($spec);
        } else {
            $class = $spec;
        }
        
        // convert the class to a base directory to stem from
        $base = str_replace('_', DIRECTORY_SEPARATOR, $class);
        
        // does the directory exist?
        $dir = Solar_Dir::exists($base);
        if (! $dir) {
            throw $this->_exception('ERR_NO_DIR_FOR_CLASS', array(
                'class' => $class,
                'base'  => $base,
            ));
        } else {
            return Solar_Dir::fix($dir . DIRECTORY_SEPARATOR. $sub);
        }
    }
}
