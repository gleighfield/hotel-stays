<?php
$xpdo_meta_map['County']= array (
  'package' => 'counties',
  'version' => '1.1',
  'table' => 'counties',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'name' => '',
    'deleted' => 0,
  ),
  'fieldMeta' => 
  array (
    'name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'deleted' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
  ),
);
