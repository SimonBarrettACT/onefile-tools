<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['ftps_server']      = getenv('FTPS_SERVER');
$config['ftps_username']    = getenv('FTPS_USERNAME');
$config['ftps_password']    = getenv('FTPS_PASSWORD');
$config['ftps_port']        = getenv('FTPS_PORT');
$config['ftps_path']        = getenv('FTPS_PATH');
$config['ftps_passive']     = getenv('FTPS_PASSIVE');
