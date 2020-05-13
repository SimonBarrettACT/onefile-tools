<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

// file: application/libraries/Env.php
class Env
{
    public function __construct()
    {

        //Only initialize this library if .env exists
        if(file_exists(APPPATH . 'environment/.env')):
            $dotenv = Dotenv\Dotenv::create(APPPATH . 'environment');
            $dotenv->load();
        endif;

        //Only initialize this library if .env exists
        if(file_exists('/webroot/.env')):
            $dotenv = Dotenv\Dotenv::create('/webroot');
            $dotenv->load();
        endif;

    }
}
