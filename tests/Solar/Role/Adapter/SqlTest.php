<?php

require_once dirname(dirname(__FILE__)) . '/AdapterTestCase.php';

class Solar_Role_Adapter_SqlTest extends Solar_Role_AdapterTestCase {
    
    protected $_sql;
    
    public function setup()
    {
        $this->_sql = Solar::factory(
            'Solar_Sql',
            array(
                'adapter' => 'Solar_Sql_Adapter_Sqlite',
                'name'    => ':memory:',
            )
        );
        
        $cmd = "
            CREATE TABLE roles (
                  handle VARCHAR(255)
                 ,name CHAR(32)
            )";
        
        $this->_sql->query($cmd);
        
        $roles = parse_ini_file(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'roles.ini', true);
        foreach ($roles as $role => $val) {
            $handles = explode(',', $val['handles']);
            $insert = array();
            foreach ($handles as $handle) {
                $data['handle'] = trim($handle);
                $data['name']   = $role;
                $this->_sql->insert('roles', $data);
            }
        }
        
        $this->_config['table']      = 'roles';
        $this->_config['handle_col'] = 'handle';
        $this->_config['role_col']   = 'name';
        $this->_config['sql']         = $this->_sql;
        
        parent::setup();
    }
}
