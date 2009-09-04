<?php return array (
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
  'name' => 
  array (
    'name' => 'name',
    'type' => 'varchar',
    'size' => 127,
    'scope' => NULL,
    'default' => NULL,
    'require' => true,
    'primary' => false,
    'autoinc' => false,
  ),
  'owner_handle' => 
  array (
    'name' => 'owner_handle',
    'type' => 'varchar',
    'size' => 32,
    'scope' => NULL,
    'default' => NULL,
    'require' => false,
    'primary' => false,
    'autoinc' => false,
  ),
  'subj' => 
  array (
    'name' => 'subj',
    'type' => 'varchar',
    'size' => 255,
    'scope' => NULL,
    'default' => NULL,
    'require' => false,
    'primary' => false,
    'autoinc' => false,
  ),
  'summ' => 
  array (
    'name' => 'summ',
    'type' => 'varchar',
    'size' => 255,
    'scope' => NULL,
    'default' => NULL,
    'require' => false,
    'primary' => false,
    'autoinc' => false,
  ),
  'prefs' => 
  array (
    'name' => 'prefs',
    'type' => 'clob',
    'size' => NULL,
    'scope' => NULL,
    'default' => NULL,
    'require' => false,
    'primary' => false,
    'autoinc' => false,
  ),
);