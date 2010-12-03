<?php
/**
 * 
 * Concrete adapter class test.
 * 
 */
class Test_Solar_Auth_Storage_Adapter_Sql extends Test_Solar_Auth_Storage_Adapter {
    
    /**
     * 
     * Default configuration values.
     * 
     * @var array
     * 
     */
    protected $_Test_Solar_Auth_Storage_Adapter_Sql = array(
    );
    
    protected $_expect = array(
        'handle'  => 'pmjones',
        'moniker' => 'Paul M. Jones',
        'email'   => 'pmjones@solarphp.com',
        'uri'     => 'http://paul-m-jones.com',
    );
    
    protected $_sql;
    
    public function preTest()
    {
        $this->_sql = Solar::factory(
            'Solar_Sql',
            array(
                'adapter' => 'Solar_Sql_Adapter_Sqlite',
                'name' => ':memory:',
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
        
        $dir = Solar_Class::dir('Mock_Solar_Auth_Adapter_Ini');
        $insert = parse_ini_file($dir . 'users.ini', true);
        foreach ($insert as $handle => $data) {
            $data['handle'] = $handle;
            $data['passwd'] = hash('md5', $data['passwd']);
            $this->_sql->insert('members', $data);
        }
        
        $this->_config['sql']         = $this->_sql;
        $this->_config['table']       = 'members';
        $this->_config['email_col']   = 'email';
        $this->_config['moniker_col'] = 'moniker';
        $this->_config['uri_col']     = 'uri';
        
        parent::preTest();
    }
}
