<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

// file: application/libraries/Env.php
class Env
{
    public function __construct()
    {
        //Only load this library if .env exists

        //Normal deployment
        if(file_exists(APPPATH . 'environment/.env')):
            $dotenv = Dotenv\Dotenv::create(APPPATH . 'environment');
            $dotenv->load();
        endif;

        //Amezmo deployment
        if(file_exists('/webroot/environment')):
            $dotenv = Dotenv\Dotenv::create('/webroot/environment');
            $dotenv->load();
        endif;

    }
}
