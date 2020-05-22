<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'third_party/FTP_Implicit_SSL.php';

class Ftps {

    var $ci;
    public $connection;

    public function __construct()
    {

        $server     = env('FTPS_SERVER');
        $username   = env('FTPS_USERNAME');
        $password   = env('FTPS_PASSWORD');
        $port       = env('FTPS_PORT');
        $path       = env('FTPS_PATH');
        $passive    = env('FTPS_PASSIVE');

        // Connect using implicit SSL
        $this->connection = new FTP_Implicit_SSL($username, $password, $server, $port, $path, $passive);


    }

}
