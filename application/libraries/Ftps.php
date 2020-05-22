<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'third_party/FTP_Implicit_SSL.php';

class Ftps {

    var $ci;
    public $connection;

    public function __construct()
    {

            $server     = env('ftps_server');
            $username   = env('ftps_username');
            $password   = env('ftps_password');
            $port       = env('ftps_port');
            $path       = env('ftps_path');
            $passive    = env('ftps_passive');

        // Connect using implicit SSL
        $this->connection = new FTP_Implicit_SSL($username, $password, $server, $port, $path, $passive);


    }

}
