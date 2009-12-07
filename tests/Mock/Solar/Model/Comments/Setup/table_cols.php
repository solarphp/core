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
  'node_id' => 
  array (
    'name' => 'node_id',
    'type' => 'int',
    'size' => NULL,
    'scope' => NULL,
    'default' => NULL,
    'require' => true,
    'primary' => false,
    'autoinc' => false,
  ),
  'email' => 
  array (
    'name' => 'email',
    'type' => 'varchar',
    'size' => 255,
    'scope' => NULL,
    'default' => NULL,
    'require' => true,
    'primary' => false,
    'autoinc' => false,
  ),
  'uri' => 
  array (
    'name' => 'uri',
    'type' => 'varchar',
    'size' => 255,
    'scope' => NULL,
    'default' => NULL,
    'require' => false,
    'primary' => false,
    'autoinc' => false,
  ),
  'body' => 
  array (
    'name' => 'body',
    'type' => 'clob',
    'size' => NULL,
    'scope' => NULL,
    'default' => NULL,
    'require' => true,
    'primary' => false,
    'autoinc' => false,
  ),
);