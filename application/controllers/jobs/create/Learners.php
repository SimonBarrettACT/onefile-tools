<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;

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
class Learners extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        $apiKey = $this->uri->segment(2);

        if (env('MY_API_KEY') !== $apiKey ):
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'error' => 'You do not have authorization to access this Job.'
            ], REST_Controller::HTTP_UNAUTHORIZED); // NOT_FOUND (404) being the HTTP response code
        endif;

        $this->load->model('user_model', 'user');

    }
    
    public function index_get()
    {
        //Start rate limiter
        $rateLimiter = ratelimiter();

        // Set file properties
		$observers = 'create_learners.csv';
        $local_path = APPPATH . '/imports/';
        
		// Fetch records 
        $iteratorRecords = $this->csv->getRecords($local_path . $observers);
        $records = iterator_to_array($iteratorRecords, true);

        //Set counter
        $counter = 0;
        $failed = 0;

        foreach($records as $record):

            $createParameters = array(
                "FirstName" => $record['FirstName'],
                "LastName" => $record['LastName'],
                "Email" => $record['Email'],
                "Role" => 1
            );

            // if (isset($record['DOB'])):
            //     try {
            //         //DOB must be in d/m/Y format e.g. 23/03/1960
            //         $datetime = DateTime::createFromFormat('d/m/Y', $record['DOB']);
            //         $dob = $datetime->format(DateTime::ATOM);
            //         $createParameters['DOB'] = $dob;
            //     } catch (Exception $e) {
            //         //echo 'Caught exception: ',  $e->getMessage(), "\n";
            //     }
            // endif;

            try {
                $response = $this->user->createUser($createParameters);
                ++$counter;
                $rateLimiter();
            } catch (Exception $e) {
                //echo 'Caught exception: ',  $e->getMessage(), "\n";
                ++$failed;
            }

        endforeach;


        $return = array('status' => true, 'message' => "Job completed. Accounts created: $counter");
        $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }    

}
