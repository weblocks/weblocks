<?php
use Phalcon\Db\Column as Column;
use Phalcon\Db\Index as Index;

return [
  'name' => 'nodes',
  'table' => [
    'columns' => [
      new Column('node_id',      ['type' => Column::TYPE_INTEGER,  'notNull' => true, 'default' => 0,   'size' =>  16, 'unsigned' => true, 'autoIncrement' => true, 'primary' => true]),
      new Column('node_model',   ['type' => Column::TYPE_VARCHAR,  'notNull' => true, 'default' => '',  'size' => 128]),
      new Column('node_name',    ['type' => Column::TYPE_VARCHAR,  'notNull' => true, 'default' => '',  'size' => 128]),
      new Column('node_row',     ['type' => Column::TYPE_INTEGER,  'notNull' => true, 'default' => 0,   'size' =>  16, 'unsigned' => true]),
      new Column('node_value',   ['type' => Column::TYPE_VARCHAR,  'notNull' => true, 'default' => '',  'size' => 512]),
      new Column('node_active',  ['type' => Column::TYPE_VARCHAR,  'notNull' => true, 'default' => 'y', 'size' =>   1]),
      new Column('node_creator', ['type' => Column::TYPE_INTEGER,  'notNull' => true, 'default' => 0,   'size' =>  16, 'unsigned' => true]),
      new Column('node_time',    ['type' => Column::TYPE_DATETIME, 'notNull' => true, 'default' => 'CURRENT_TIMESTAMP']),
    ],
    'indexes' => [
      new Index('indx001', ['node_model', 'node_name', 'node_row', 'node_active']),
    ],
  ],
  'data' => [
    'fields' => ['node_model', 'node_name', 'node_row', 'node_value'],
    'values' => [
      ['roles', 'id', 0, '1'],
      ['roles', 'id', 1, '2'],
      ['roles', 'id', 2, '3'],
      ['roles', 'id', 3, '4'],
      ['roles', 'name', 0, 'administrator'],
      ['roles', 'name', 1, 'designer'],
      ['roles', 'name', 2, 'writer'],
      ['roles', 'name', 3, 'reader'],
      ['roles', 'inherit', 0, '2'],
      ['roles', 'inherit', 1, '3'],
      ['roles', 'inherit', 2, '4'],
      ['roles', 'inherit', 3, '0'],
      ['homes', 'id',    0, '1'],
      ['homes', 'auth',  0, 'role'],
      ['homes', 'model', 0, 'admin'],
      ['homes', 'owner', 0, '1'],
    ],
  ],
  'views' => [
    [
      'name' => 'roles',
      'sql'  => "select
                 max(case when node_name = 'id' then node_value else null end) as id,
                 max(case when node_name = 'name' then node_value else null end) as name,
                 max(case when node_name = 'inherit' then node_value else null end) as inherit
                 from nodes
                 where node_model = 'roles'
                 group by node_row
                 order by node_row"
    ],
    [
      'name' => 'users',
      'sql'  => "select
                 max(case when node_name = 'id' then node_value else null end) as id,
                 max(case when node_name = 'name' then node_value else null end) as name,
                 max(case when node_name = 'password' then node_value else null end) as password,
                 max(case when node_name = 'role' then node_value else null end) as role
                 from nodes
                 where node_model = 'users'
                 group by node_row
                 order by node_row"
    ],
    [
      'name' => 'homes',
      'sql'  => "select
                 max(case when node_name = 'id' then node_value else null end) as id,
                 max(case when node_name = 'auth' then node_value else null end) as auth,
                 max(case when node_name = 'model' then node_value else null end) as model,
                 max(case when node_name = 'owner' then node_value else null end) as owner
                 from nodes
                 where node_model = 'homes'
                 group by node_row
                 order by node_row"
    ],
  ],
];
