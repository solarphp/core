<?php
/**
 * 
 * Class for reading access privileges from a text file.
 * 
 * @category Solar
 * 
 * @package Solar_Access
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * 
 * Class for reading access privileges from a text file.
 * 
 * The file format is ...
 * 
 *     0:flag 1:type 2:name 3:class 4:action 5:process
 * 
 * For example ...
 * 
 *     deny handle * * * * 
 *     allow role sysadmin * * * 
 *     allow handle + Solar_App_Bookmarks * * 
 *     deny user boshag Solar_App_Bookmarks edit * 
 * 
 * @category Solar
 * 
 * @package Solar_Access
 * 
 */
class Solar_Access_Adapter_File extends Solar_Access_Adapter {
    
    /**
     * 
     * User-provided configuration values.
     * 
     * @var array
     * 
     */
    protected $_Solar_Access_Adapter_File = array(
        'file'   => '/path/to/access.txt',
    );
    
    /**
     * 
     * Fetch access privileges for a user handle and roles.
     * 
     * @param string $handle The user handle.
     * 
     * @param array $roles The user roles.
     * 
     * @return array
     * 
     */
    public function fetch($handle, $roles)
    {
        // force the full, real path to the file
        $file = realpath($this->_config['file']);
        
        // does the file exist?
        if (! Solar::fileExists($file)) {
            throw $this->_exception(
                'ERR_FILE_NOT_READABLE',
                array('file' => $file)
            );
        }
        
        $handle = trim($handle);
        
        // eventual access list for the handle and roles
        $list = array();
        
        // get the access source and split into lines
        $src = file_get_contents($this->_config['file']);
        $src = preg_replace('/[ \t]{2,}/', ' ', trim($src));
        $lines = explode("\n", $src);
        
        foreach ($lines as $line) {
            // $info keys are ...
            // 0 => "allow" or "deny"
            // 1 => "handle" or "role"
            // 2 => handle/role name
            // 3 => class name
            // 4 => action name
            // 5 => process name
            $info = explode(' ', $line);
            if ($info[1] == 'handle' && $info[2] == $handle ||        // direct user handle match
                $info[1] == 'handle' && $info[2] == '+' && $handle || // any authenticated user
                $info[1] == 'handle' && $info[2] == '*' ||            // any user (incl anon)
                $info[1] == 'role'   && in_array($info[2], $roles) || // direct role match
                $info[2] == 'role'   && $info[2] == '*') {            // any role (incl anon)
                
                // keep the line
                $list[] = array(
                    'allow'   => ($info[0] == 'allow' ? true : false),
                    'class'   => $info[3],
                    'action'  => $info[4],
                    'process' => $info[5],
                );
            }
        }
        return $list;
    }
}
