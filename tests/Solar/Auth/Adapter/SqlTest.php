<?php

require_once dirname(dirname(__FILE__)) . '/AdapterTestCase.php';

class Solar_Auth_Adapter_SqlTest extends Solar_Auth_AdapterTestCase {
    
    protected $_sql;
    
    public function setup()
    {
        $this->_sql = Solar::factory(
            'Solar_Sql',
            array(
                'adapter' => 'Solar_Sql_Adapter_Sqlite',
                'config' => array(
                    'name' => ':memory:',
                )
            )
        );
        
        $cmd = "CREATE TABLE members ("
             . "    handle VARCHAR(255),"
             . "    passwd CHAR(32),"
             . "    email VARCHAR(255),"
             . "    moniker VARCHAR(255),"
             . "    uri VARCHAR(255)"
             . ")";
        
        $this->_sql->query($cmd);
        
        $this->_moniker = 'Paul M. Jones';
        $this->_email = 'pmjones@solarphp.com';
        $this->_uri = 'http://paul-m-jones.com';
        
        $insert = parse_ini_file(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'users.ini', true);
        foreach ($insert as $handle => $data) {
            $data['handle'] = $handle;
            $data['passwd'] = md5($data['passwd']);
            $this->_sql->insert('members', $data);
        }
        
        $this->_config['table']       = 'members';
        $this->_config['email_col']   = 'email';
        $this->_config['moniker_col'] = 'moniker';
        $this->_config['uri_col']     = 'uri';
        $this->_config['sql']         = $this->_sql;
        
        parent::setup();
    }
}
?>