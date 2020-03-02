<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Accounts extends CI_Controller {

    public function __construct()
    {
            parent::__construct();

            if (!is_cli()) :
                // echo "<p>These tools can only be run from the command line.</p>";
                // die();
            endif;
    }
    
    public function index()
    {
       // echo $this->user->getUsers();
        echo $this->user->getUser(882032);
    }
}
