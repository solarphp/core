<?php
abstract class Test_Solar_Example_Model extends Solar_Test
{
    public function setup()
    {
        parent::setup();
        Solar_Registry::set('sql', 'Solar_Sql');
    }
}