<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

    public function __construct()
    {
            parent::__construct();
    }

    public function index()
    {
        echo $this->classroom->getClassrooms();

        // echo "This is a utility for OneFile.".PHP_EOL;
    }
}
