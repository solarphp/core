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
  'node_id' => 
  array (
    'name' => 'node_id',
    'type' => 'int',
    'size' => NULL,
    'scope' => NULL,
    'default' => '',
    'require' => true,
    'primary' => false,
    'autoinc' => false,
  ),
  'last_comment_id' => 
  array (
    'name' => 'last_comment_id',
    'type' => 'int',
    'size' => NULL,
    'scope' => NULL,
    'default' => '',
    'require' => false,
    'primary' => false,
    'autoinc' => false,
  ),
  'last_comment_by' => 
  array (
    'name' => 'last_comment_by',
    'type' => 'varchar',
    'size' => 255,
    'scope' => NULL,
    'default' => '',
    'require' => false,
    'primary' => false,
    'autoinc' => false,
  ),
  'last_comment_at' => 
  array (
    'name' => 'last_comment_at',
    'type' => 'timestamp',
    'size' => NULL,
    'scope' => NULL,
    'default' => '',
    'require' => false,
    'primary' => false,
    'autoinc' => false,
  ),
  'comment_count' => 
  array (
    'name' => 'comment_count',
    'type' => 'int',
    'size' => NULL,
    'scope' => NULL,
    'default' => '',
    'require' => false,
    'primary' => false,
    'autoinc' => false,
  ),
);