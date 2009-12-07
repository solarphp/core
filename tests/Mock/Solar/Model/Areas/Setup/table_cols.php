<?php
return array (
  'id' => 
  array (
    'name' => 'id',
    'type' => 'int',
    'size' => NULL,
    'scope' => NULL,
    'default' => NULL,
    'require' => true,
    'primary' => true,
    'autoinc' => true,
  ),
  'created' => 
  array (
    'name' => 'created',
    'type' => 'timestamp',
    'size' => NULL,
    'scope' => NULL,
    'default' => NULL,
    'require' => false,
    'primary' => false,
    'autoinc' => false,
  ),
  'updated' => 
  array (
    'name' => 'updated',
    'type' => 'timestamp',
    'size' => NULL,
    'scope' => NULL,
    'default' => NULL,
    'require' => false,
    'primary' => false,
    'autoinc' => false,
  ),
  'user_id' => 
  array (
    'name' => 'user_id',
    'type' => 'int',
    'size' => NULL,
    'scope' => NULL,
    'default' => '',
    'require' => true,
    'primary' => false,
    'autoinc' => false,
  ),
  'name' => 
  array (
    'name' => 'name',
    'type' => 'varchar',
    'size' => 127,
    'scope' => NULL,
    'default' => NULL,
    'require' => false,
    'primary' => false,
    'autoinc' => false,
  ),
);