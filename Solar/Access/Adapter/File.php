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
 * Abstract access adapter class.
 */
Solar::loadClass('Solar_Access_Adapter');

/**
 * 
 * Class for reading access privileges from a text file.
 * 
 * The file format is:
 *
 * 0:flag 1:type 2:name 3:class 4:action 5:submit
 * 
 * E.g.:
 * 
 * <code>
 * deny handle * * * *
 * allow role sysadmin * * *
 * allow handle + Solar_App_Bookmarks * *
 * deny user boshag Solar_App_Bookmarks edit *
 * </code>
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
        $handle = trim($handle);
        if (! $handle) {
            $handle = '*';
        }
        
        // eventual access list for the handle and roles
        $list = array();
        
        // get the access source and split into lines
        $src = file_get_contents($this->_config['file']);
        $src = preg_replace('/[ \t]{2,}/', ' ', trim($src));
        $lines = explode("\n", $src);
        
        foreach ($lines as $line) {
            // $info keys are:
            // 0 => "allow" or "deny"
            // 1 => "handle" or "role"
            // 2 => handle/role name
            // 3 => class name
            // 4 => action name
            // 5 => submit name
            $info = explode(' ', $line);
            if ($info[1] == 'handle' && $info[2] == $handle ||        // direct user handle match
                $info[1] == 'handle' && $info[2] == '+' && $handle || // any authenticated user
                $info[1] == 'handle' && $info[2] == '*' ||            // any 
                $info[1] == 'role'   && in_array($info[2], $roles) || // direct role match
                $info[2] == 'role'   && $info[2] == '*') {            // any role
                
                // keep the line
                $list[] = array(
                    'allow'  => ($info[0] == 'allow' ? true : false),
                    'class'  => $info[3],
                    'action' => $info[4],
                    'submit' => $info[5],
                );
            }
        }
        return $list;
    }
}
?>