<?php
abstract class Test_Solar_Example_Model extends Solar_Test
{
    public function setup()
    {
        parent::setup();
        Solar_Registry::set('sql', 'Solar_Sql');
        Solar_Registry::set('model_catalog', 'Solar_Sql_Model_Catalog');
    }
}