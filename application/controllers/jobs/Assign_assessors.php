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
class Assign_assessors extends REST_Controller {

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

        //Set standard IDs
        $standards['AON']['L1'] = 41240;
        $standards['AON']['L2'] = 41242;
        $standards['AON']['L3'] = 41250;

        $standards['COM']['L1'] = 41258;
        $standards['COM']['L2'] = 41257;
        $standards['COM']['L3'] = 41259;

        $standards['DL']['L1'] = 41260;
        $standards['DL']['L2'] = 41261;
        $standards['DL']['L3'] = 41288;

        // Set file properties
		$learners = 'assign_assessors.csv';
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

        $failedRequests = [];
        $successRequests = [];
        
        //Loop through the records
        foreach($records as $record):
            //Assessor
            $assessorID = intval($record['AssessorID']);
            $assessorFirst = $record['AssessorFirst'];
 
            $firstName = trim($record['FirstName']);
            $lastName = trim($record['LastName']);

            //Find the learner from their first and second name
            $keyFound = search_users($onefileLearners, false, $firstName, 'FirstName', $lastName, 'LastName');

            if ($keyFound) :
                $learnerID = intval($onefileLearners[$keyFound]['ID']);
                $firstName = $onefileLearners[$keyFound]['FirstName'];
                $lastName = $onefileLearners[$keyFound]['LastName'];
                $assessor = $assessorFirst ;

                $aon = $record['AON'];
                $com = $record['Communication'];
                $dl = $record['DL'];

                $level = 3; //Learning Aim

                $currentRequest = array("LearnerID" => $learnerID, "FirstName" => $firstName, "LastName" => $lastName, "Tutor" => $assessor, "Comms" => $com, "AON" => $aon, "DL" => $dl);

                //Assign AON
                if(isset($standards['AON'][$aon])):
                    $standard = $standards['AON'][$aon];
                    $assignParameters = array("LearnerID" => $learnerID, "Level" => $level, "StandardID" => $standard);
                    try {
                        $response = $this->user->assignUser($assessorID, $assignParameters);
                        ++$counter;
                        $successRequests[] = $currentRequest;
                    } catch (Exception $e) {
                        $failedRequests[] = $currentRequest;
                        //echo 'Caught exception: ',  $e->getMessage(), "\n";
                        ++$failed;
                    }
                endif;

                //Assign Communications
                if(isset($standards['COM'][$com])):
                    $standard = $standards['COM'][$com];
                    $assignParameters = array("LearnerID" => $learnerID, "Level" => $level, "StandardID" => $standard);
                    try {
                        $this->user->assignUser($assessorID, $assignParameters);
                        ++$counter;
                        $successRequests[] = $currentRequest;
                    } catch (Exception $e) {
                        $failedRequests[] = $currentRequest;
                        //echo 'Caught exception: ',  $e->getMessage(), "\n";
                        ++$failed;
                    }
                endif;

                //Assign Digital Literacy
                if(isset($standards['DL'][$dl])):
                    $standard = $standards['DL'][$dl];
                    $assignParameters = array("LearnerID" => $learnerID, "Level" => $level, "StandardID" => $standard);
                    try {
                        $this->user->assignUser($assessorID, $assignParameters);
                        ++$counter;
                        $successRequests[] = $currentRequest;
                      } catch (Exception $e) {
                        $failedRequests[] = $currentRequest;
                        //echo 'Caught exception: ',  $e->getMessage(), "\n";
                        ++$failed;
                    }
                endif;

            endif;
            
        endforeach;
        
        $return = array('status' => true, 'message' => "Job completed with $counter assignments and $failed failures.");
        $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }    

}
