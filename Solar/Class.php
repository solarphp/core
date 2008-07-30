<?php
class Solar_Class
{
    /**
     * 
     * Parent hierarchy for all classes.
     * 
     * We keep track of this so configs, locale strings, etc. can be
     * inherited properly from parent classes.
     * 
     * Although this property is public, you generally shouldn't need
     * to manipulate it in any way.
     * 
     * @var array
     * 
     */
    public static $parents = array();
    
    /**
     * 
     * Returns an array of the parent classes for a given class.
     * 
     * Parents in "reverse" order ... element 0 is the immediate parent,
     * element 1 the grandparent, etc.
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
        if (empty(self::$parents[$class])) {
            // get the stack of classes leading to this one
            self::$parents[$class] = array();
            $parent = $class;
            while ($parent = get_parent_class($parent)) {
                self::$parents[$class][] = $parent;
            }
        }
        
        // get the parent stack
        $stack = self::$parents[$class];
        
        // add the class itself?
        if ($include_class) {
            array_unshift($stack, $class);
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
        
        // if we have a static include directory, use it
        if (Solar::$include) {
            $base = Solar::$include . DIRECTORY_SEPARATOR . $base;
        }
        
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
