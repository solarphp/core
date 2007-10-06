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
    'default' => '',
    'require' => true,
    'primary' => false,
    'autoinc' => false,
  ),
  'updated' => 
  array (
    'name' => 'updated',
    'type' => 'timestamp',
    'size' => NULL,
    'scope' => NULL,
    'default' => '',
    'require' => true,
    'primary' => false,
    'autoinc' => false,
  ),
  'handle' => 
  array (
    'name' => 'handle',
    'type' => 'varchar',
    'size' => 32,
    'scope' => NULL,
    'default' => '',
    'require' => true,
    'primary' => false,
    'autoinc' => false,
  ),
);