<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Placement extends CI_Controller {

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
        echo $this->placement->getPlacements();
    }

    public function id($id)
    {
        
        echo $this->placement->getPlacement($id);

    }

}
