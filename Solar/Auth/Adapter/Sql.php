<?php
/**
 * 
 * Authenticate against an SQL database table.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id$
 * 
 */

/**
 * Authentication adapter class.
 */
Solar::loadClass('Solar_Auth_Adapter');

/**
 * 
 * Authenticate against an SQL database table.
 * 
 * @category Solar
 * 
 * @package Solar_Auth
 * 
 * @todo add support for email, moniker, uri retrieval
 * 
 */
class Solar_Auth_Adapter_Sql extends Solar_Auth_Adapter {
    
    /**
     * 
     * User-supplied configuration values.
     * 
     * Keys are:
     * 
     * : \\sql\\ : (string|array) How to get the SQL object.  If a string, is
     * treated as a Solar::registry() object name.  If array, treated as
     * config for a standalone Solar_Sql object.
     * 
     * : \\table\\ : (string) Name of the table holding authentication data.
     * 
     * : \\handle_col\\ : (string) Name of the column with the handle.
     * 
     * : \\passwd_col\\ : (string) Name of the column with the MD5-hashed passwd.
     * 
     * : \\email_col\\ : (string) Name of the column with the email address.
     * 
     * : \\moniker_col\\ : (string) Name of the column with the display name (moniker).
     * 
     * : \\uri_col\\ : (string) Name of the column with the website URI.
     * 
     * : \\salt\\ : (string) A salt prefix to make cracking passwords harder.
     * 
     * : \\where\\ : (string|array) Additional _multiWhere() conditions to use
     *   when selecting rows for authentication.
     * 
     * @var array
     * 
     */
    protected $_config = array(
        'sql'         => 'sql',
        'table'       => 'members',
        'handle_col'  => 'handle',
        'passwd_col'  => 'passwd',
        'email_col'   => null,
        'moniker_col' => null,
        'uri_col'     => null,
        'salt'        => null,
        'where'       => array(),
    );
    
    /**
     * 
     * Verifies a username handle and password.
     * 
     * @return bool True if valid, false if not.
     * 
     */
    protected function _verify()
    {
        $handle = $this->_handle;
        $passwd = $this->_passwd;
        
        // get the dependency object of class Solar_Sql
        $obj = Solar::dependency('Solar_Sql', $this->_config['sql']);
        
        // get a selection tool using the dependency object
        $select = Solar::factory(
            'Solar_Sql_Select',
            array('sql' => $obj)
        );
        
        // always get the user handle
        $cols = array($this->_config['handle_col']);
        
        // get the display name (moniker)?
        if ($this->_config['moniker_col']) {
            $cols[] = $this->_config['moniker_col'];
        }
        
        // get the email address?
        if ($this->_config['email_col']) {
            $cols[] = $this->_config['email_col'];
        }
        
        // get the uri website?
        if ($this->_config['uri_col']) {
            $cols[] = $this->_config['uri_col'];
        }
        
        // salt and hash the password
        $md5 = md5($this->_config['salt'] . $passwd);
        
        // build the select
        $select->from($this->_config['table'], $cols)
               ->where("{$this->_config['handle_col']} = ?", $handle)
               ->where("{$this->_config['passwd_col']} = ?", $md5)
               ->multiWhere($this->_config['where']);
               
        // get the results
        $rows = $select->fetch('all');
        
        // if we get back exactly 1 row, the user is authenticated;
        // otherwise, it's more or less than exactly 1 row.
        if (count($rows) == 1) {
            $row = $rows->current();
            $this->_email   = $row[$this->_config['email_col']];
            $this->_moniker = $row[$this->_config['moniker_col']];
            $this->_uri     = $row[$this->_config['uri_col']];
            return true;
        } else {
            return false;
        }
    }
}
?>