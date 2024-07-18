<?php

return array (
  'default' => 'data_studio',
  'connections' =>
  array (
    'data_studio' =>
    array (
      'driver' => 'mysql',
      'url' => NULL,
      'host' => '127.0.0.1',
      'port' => '3306',
      'database' => 'data_studio',
      'username' => 'root',
      'password' => '',
      'unix_socket' => '',
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_unicode_ci',
      'prefix' => '',
      'prefix_indexes' => true,
      'strict' => true,
      'engine' => NULL,
      'options' =>
      array (
      ),
    ),
    'lululemon' =>
    array (
      'driver' => 'mysql',
      'url' => NULL,
      'host' => '127.0.0.1',
      'port' => '3306',
      'database' => 'lululemon',
      'username' => 'root',
      'password' => '',
      'unix_socket' => '',
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_unicode_ci',
      'prefix' => '',
      'prefix_indexes' => true,
      'strict' => true,
      'engine' => NULL,
      'options' =>
      array (
      ),
    ),
    'klm' =>
    array (
      'driver' => 'mysql',
      'url' => NULL,
      'host' => '127.0.0.1',
      'port' => '3306',
      'database' => 'klm',
      'username' => 'root',
      'password' => '',
      'unix_socket' => '',
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_unicode_ci',
      'prefix' => '',
      'prefix_indexes' => true,
      'strict' => true,
      'engine' => NULL,
      'options' =>
      array (
      ),
    ),
    'suntory' =>
    array (
      'driver' => 'mysql',
      'url' => NULL,
      'host' => '127.0.0.1',
      'port' => '3306',
      'database' => 'suntory',
      'username' => 'root',
      'password' => '',
      'unix_socket' => '',
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_unicode_ci',
      'prefix' => '',
      'prefix_indexes' => true,
      'strict' => true,
      'engine' => NULL,
      'options' =>
      array (
      ),
    ),
    'popin' =>
    array (
      'driver' => 'mysql',
      'host' => '127.0.0.1',
      'port' => '3306',
      'database' => 'popin',
      'username' => 'root',
      'password' => '',
      'unix_socket' => '',
      'charset' => 'utf8mb4',
      'collation' => 'utf8mb4_unicode_ci',
      'prefix' => '',
      'prefix_indexes' => true,
      'strict' => true,
      'engine' => NULL,
      'options' =>
      array (
      ),
    ),
  ),
  'migrations' =>
  array (
    'table' => 'migrations',
    'update_date_on_publish' => true,
  ),
  'redis' =>
  array (
    'client' => 'phpredis',
    'options' =>
    array (
      'cluster' => 'redis',
      'prefix' => 'laravel_database_',
    ),
    'default' =>
    array (
      'url' => NULL,
      'host' => '127.0.0.1',
      'username' => NULL,
      'password' => NULL,
      'port' => '6379',
      'database' => '0',
    ),
    'cache' =>
    array (
      'url' => NULL,
      'host' => '127.0.0.1',
      'username' => NULL,
      'password' => NULL,
      'port' => '6379',
      'database' => '1',
    ),
  ),
);

