<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;
use League\Csv\Writer;

include APPPATH . 'third_party/Filters.php';

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
class Standard extends REST_Controller {

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
        $this->load->model('standard_model', 'standard');
        

    }
    
    public function index_get()
    {

        // Set file properties
		$learners = 'assign_standards.csv';
        $local_path = APPPATH . '/imports/';
        
		// Fetch records 
        $iteratorRecords = $this->csv->getRecords($local_path . $learners);
        $records = iterator_to_array($iteratorRecords, true);

        //Get all the learners from OneFile
        $json = $this->user->getUsers();
        $onefileLearners = json_decode($json, true);

        //Set counter
        $counter = 0;
        $failed = 0;
        
        //Loop through the records
        foreach($records as $record):
 
            $firstName = trim($record['FirstName']);
            $lastName = trim($record['LastName']);

            $standardID = intval($record['StandardID']);

            //Find the learner from their first and second name
            $keyFound = search_users($onefileLearners, false, $firstName, 'FirstName', $lastName, 'LastName');

            if ($keyFound) :
                $learnerID = intval($onefileLearners[$keyFound]['ID']);

                try {
                    $response = $this->standard->assignStandard($standardID, $learnerID);
                    ++$counter;
                } catch (Exception $e) {
                    //echo 'Caught exception: ',  $e->getMessage(), "\n";
                    ++$failed;
                }

            endif;
            
        endforeach;
        
        $return = array('status' => true, 'message' => "Job completed with $counter assignments and $failed failures.");
        $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }    

  
}
