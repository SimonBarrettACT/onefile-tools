<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class User extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        $apiKey = $this->uri->segment(2);

        if (env('MY_API_KEY') !== $apiKey ):
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'error' => 'You do not have authorization to access this API.'
            ], REST_Controller::HTTP_UNAUTHORIZED); // NOT_FOUND (404) being the HTTP response code
        endif;

        $this->load->model('user_model', 'user');

    }
 
    //Get all users (Learners)
    public function index_post()
    {
        $json = $this->user->getUsers();
        $return = json_decode($json, true);

        if (!empty($return))
        {
            $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->set_response([
                'status' => FALSE,
                'error' => 'Users could not be found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }


    }   

    public function search_post($pageNumber=0,$pageSize=50)
    {
        $parameters = $this->post();
        $json = $this->user->getUsers($parameters, $pageNumber, $pageSize);


        $return = json_decode($json, true);

        $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

    }


    public function id_get($id)
    {

        // Find and return a single record for a particular standard.

        $id = (int) $id;

        // Validate the id.
        if ($id <= 0)
        {
            // Invalid id, set the response and exit.
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        $json = $this->user->getUser($id);
        $return = json_decode($json, true);

        if (!empty($return))
        {
            $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->set_response([
                'status' => FALSE,
                'error' => 'User could not be found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }


    }

    public function id_delete($id)
    {
        $id = (int) $id;

        // Validate the id.
        if ($id <= 0)
        {
            // Invalid id, set the response and exit.
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        $json = $this->user->deleteUser($id);
        $return = json_decode($json, true);

        if (!empty($return))
        {
            $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->set_response([
                'status' => FALSE,
                'error' => 'User could not be found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }

    }

    // public function assign_post($standardId, $learnerId)
    // {

    //     // Assign a standard
    //     $standardId = (int) $standardId;
    //     $learnerId = (int) $learnerId;

    //     // Validate the id.
    //     if (($standardId <= 0) or ($learnerId <= 0))
    //     {
    //         // Invalid id, set the response and exit.
    //         $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
    //     }

    //     $return = $this->standard->assignStandard($standardId, $learnerId);

    //     if ($return == true)
    //     {
    //         $this->set_response([
    //             'status' => TRUE,
    //             'message' => 'Standard has been assigned'
    //         ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    //     }
    //     else
    //     {
    //         $this->set_response([
    //             'status' => FALSE,
    //             'error' => 'Standard could not be found'
    //         ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
    //     }


    // } 

    // public function search_post($pageNumber=0,$pageSize=50)
    // {
        
    //     $parameters = $this->post();
    //     $json = $this->standard->getStandards($parameters, $pageNumber, $pageSize);

    //     $return = json_decode($json, true);

    //     $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    
    // }

        //Get unit summary for user
        public function unitsummary_get($id)
        {

            $id = (int) $id;

            // Validate the id.
            if ($id <= 0)
            {
                // Invalid id, set the response and exit.
                $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
            }

            $json = $this->user->getUserUnitSummary($id);
            $return = json_decode($json, true);
    
            if (!empty($return))
            {
                $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
            else
            {
                $this->set_response([
                    'status' => FALSE,
                    'error' => 'Unit summaries could not be found'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
    
    
        } 

}
