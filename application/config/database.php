<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
    'dsn'   => '',
    'hostname' => '127.0.0.1',
    'username' => 'root',
    'password' => 'root',
    'database' => 'holasync_toopai',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    // 'db_debug' => (ENVIRONMENT !== 'production'),
    'db_debug' => (ENVIRONMENT !== 'development'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_unicode_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE,
    // 'connect_timeout' => 120,  // Timeout 2 menit
    // 'options' => array(
    //     MYSQLI_OPT_CONNECT_TIMEOUT => 300,
    //     MYSQLI_READ_DEFAULT_FILE => "1",
    // ),
    'port' => 3306,
);