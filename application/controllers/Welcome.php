<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

    public function __construct()
    {
            parent::__construct();

            if (!is_cli()) :
                echo "<p>These tools can only be run from the command line.</p>";
                die();
            endif;
    }

    public function index()
    {
        echo "This is a CLI utility for OneFile.".PHP_EOL;
    }
}
