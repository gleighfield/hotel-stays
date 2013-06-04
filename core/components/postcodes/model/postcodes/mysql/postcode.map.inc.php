<?php
$xpdo_meta_map['Postcode']= array (
  'package' => 'postcodes',
  'version' => '1.1',
  'table' => 'postcodes',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'name' => '',
    'lat' => '',
    'lng' => '',
  ),
  'fieldMeta' => 
  array (
    'name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '10',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'lat' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '15',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'lng' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '15',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
  ),
);
