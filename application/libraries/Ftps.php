<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH . 'third_party/FTP_Implicit_SSL.php';

class Ftps {

    var $ci;
    public $connection;

    public function __construct()
    {
            
            $this->ci =& get_instance();
            if (file_exists(APPPATH . 'config/ftps_local.php')):
                $this->ci->config->load('ftps_local');            
            else:
                $this->ci->config->load('ftps');
            endif;

            $server     = $this->ci->config->item('ftps_server');
            $username   = $this->ci->config->item('ftps_username');
            $password   = $this->ci->config->item('ftps_password');
            $port       = $this->ci->config->item('ftps_port');
            $path       = $this->ci->config->item('ftps_path');
            $passive    = $this->ci->config->item('ftps_passive');

        // Connect using implicit SSL
        $this->connection = new FTP_Implicit_SSL($username, $password, $server, $port, $path, $passive);


    }

}
