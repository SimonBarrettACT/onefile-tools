<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

// file: application/libraries/Env.php
class Env
{
    public function __construct()
    {

        //Check for local .env first
        if(file_exists(APPPATH . 'environment/.env')):
            $dotenv = Dotenv\Dotenv::create(APPPATH . 'environment');
            $dotenv->load();
        else:
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
            $dotenv->load();
        endif;

    }
}
