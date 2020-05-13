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
            $dotenv = Dotenv\Dotenv::createImmutable(APPPATH . 'environment');
            $dotenv->load();
        else:
            $envPath = s(FCPATH)->replaceSuffix('/public/');
            $dotenv = Dotenv\Dotenv::createImmutable($envPath);
            $dotenv->load();
        endif;

    }
}
