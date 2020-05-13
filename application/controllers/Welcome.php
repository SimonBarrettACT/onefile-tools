<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

    public function __construct()
    {
            parent::__construct();
    }

    public function index()
    {
        $root = env('APPLICATION_ROOT');

        echo "<p>This is a utility for OneFile.</p>";
        echo "<p>$root</p>";
    }
}
