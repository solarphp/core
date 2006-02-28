<?php
/**
 * 
 * Stack for finding files in user-defined fallback paths.
 * 
 * @category Solar
 * 
 * @package Solar_PathStack
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
 * Stack for finding files in user-defined fallback paths.
 * 
 * As you add directory paths, they are searched first when you call
 * find($file).  This allows users to add override paths so their files will
 * be used instead of default files.
 * 
 * @category Solar
 * 
 * @package Solar_PathStack
 * 
 */
class Solar_PathStack {
    
    /**
     * 
     * The stack of directories.
     * 
     * @var array
     */
    protected $_stack = array();
    
    /**
     * 
     * Gets a copy of the current stack.
     * 
     * @var array
     */
    public function get()
    {
        return $this->_stack;
    }
    
    /**
     * 
     * Adds one or more directories to the stack.
     * 
     * For example:
     * 
     * <code type="php">
     * $stack = Solar::factory('Solar_PathStack');
     * $stack->add('path/1');
     * $stack->add('path/2');
     * $stack->add('path/3');
     * 
     * // $stack->get() reveals that the directory search order will be
     * // 'path/3/', 'path/2/', 'path/1/', because the later adds
     * // override the newer ones.
     *
     * $stack = Solar::factory('Solar_PathStack');
     * $stack->add(array('path/1', 'path/2', 'path/3'));
     * // as before, $stack->get() reveals that the directory search order will be
     * // 'path/3/', 'path/2/', 'path/1/', because the later adds
     * // override the newer ones.
     * 
     * $stack = Solar::factory('Solar_PathStack');
     * $stack->add('path/1:path/2:path/3');
     * // in this case, $stack->get() reveals that the directory search order will be
     * // 'path/1/', 'path/2/', 'path/3/', because this is the way the
     * // filesystem expects a path definition to work.
     * 
     * </code>
     * 
     * @var array|string $path The directories to add to the stack.
     * 
     */
    public function add($path)
    {
        if (is_string($path)) {
            $path = array_reverse(explode(PATH_SEPARATOR, $path));
        }
        
        foreach ($path as $dir) {
            $dir = trim($dir);
            if (! $dir) {
                continue;
            }
            $k = strlen($dir) - 1;
            if ($dir[$k] != DIRECTORY_SEPARATOR) {
                $dir .= DIRECTORY_SEPARATOR;
            }
            array_unshift($this->_stack, $dir);
        }
    }
    
    /**
     * 
     * Clears the stack and adds one or more directories.
     * 
     * For example:
     * 
     * <code type="php">
     * $stack = Solar::factory('Solar_PathStack');
     * $stack->add('path/1');
     * $stack->add('path/2');
     * $stack->add('path/3');
     * 
     * // $stack->get() reveals that the directory search order is
     * // 'path/3/', 'path/2/', 'path/1/'.
     *
     * $stack->set('another/path');
     * 
     * // $stack->get() is now 'another/path'.
     * </code>
     * 
     * @var array|string $path The directories to add to the stack
     * after clearing it.
     * 
     */
    public function set($path)
    {
        $this->_stack = array();
        return $this->add($path);
    }
    
    /**
     * 
     * Finds a file in the path stack using include_path.
     * 
     * For example:
     * 
     * <code type="php">
     * $stack = Solar::factory('Solar_PathStack');
     * $stack->add('path/1');
     * $stack->add('path/2');
     * $stack->add('path/3');
     * 
     * $file = $stack->findInclude('file.php');
     * // $file is now the first instance of 'file.php' found from the         
     * // directory stack, looking first in 'path/3/file.php', then            
     * // 'path/2/file.php', then finally 'path/1/file.php'.
     *
     * </code>
     * 
     * @var string $file The file to find using the directory stack.
     * 
     */
    public function findInclude($file)
    {
        foreach ($this->_stack as $dir) {
            $spec = $dir . $file;
            if (Solar::fileExists($spec)) {
                return $spec;
            }
        }
        return false;
    }
    
    /**
     * 
     * Finds a file in the path stack using realpath().
     * 
     * While slower than findInclude(), this helps to protect against
     * directory traversal attacks.
     * 
     * For example:
     * 
     * <code type="php">
     * $stack = Solar::factory('Solar_PathStack');
     * $stack->add('path/1');
     * $stack->add('path/2');
     * $stack->add('path/3');
     * 
     * $file = $stack->findReal('file.php');
     * // $file is now the first instance of 'file.php' found from the         
     * // directory stack, looking first in 'path/3/file.php', then            
     * // 'path/2/file.php', then finally 'path/1/file.php'.
     * //
     * // note that this will be the realpath() to the file from the
     * // filesystem root.
     * </code>
     * 
     * @var string $file The file to find using the directory stack.
     * 
     */
    public function findReal($file)
    {
        foreach ($this->_stack as $dir) {
            
            // find the real paths to these items
            $realspec = realpath($dir . $file);
            $realdir  = realpath($dir);
            
            // make sure the realpath() file spec exists and is
            // readable, *and* that the real directories match.
            if (file_exists($realspec) &&
                is_readable($realspec) &&
                substr($realspec, 0, strlen($realdir)) == $realdir) {
                // found it
                return $realspec;
            }
            
        }
        return false;
    }
}
?>